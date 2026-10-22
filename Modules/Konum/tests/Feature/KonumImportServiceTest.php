<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Konum\Services\KonumFrontendService;
use Modules\Konum\Services\KonumImportService;
use Modules\Konum\Services\KonumSource;
use Modules\Konum\Services\KonumVerificationService;
use Modules\Konum\Services\PolygonGeometry;

uses(LazilyRefreshDatabase::class);

require_once __DIR__.'/../Fixtures/source.php';

test('imports all five levels without executing SQL and repeats without duplicating records', function () {
    Http::preventStrayRequests();
    $directory = konumFixtureSource();
    $first = app(KonumImportService::class)->import($directory);
    $second = app(KonumImportService::class)->import($directory);

    expect($second)->toBe($first);
    foreach (KonumSource::TABLES as $table) {
        expect($second[$table])->toMatchArray(['source_count' => 1, 'target_count' => 1, 'orphans' => 0, 'hierarchy_mismatches' => 0, 'duplicate_ids' => 0]);
    }
    $this->assertDatabaseHas('konum_neighbourhoods', ['id' => 1, 'zip_code' => '01720']);
    $this->assertDatabaseHas('konum_streets', ['name' => "O'Neil, Yeni (Sokak)"]);
    $this->assertDatabaseHas('konum_imports', ['status' => 'complete']);
    $hex = DB::table('konum_cities')->value('polygons');
    expect(app(PolygonGeometry::class)->decode($hex))->toMatchArray(['type' => 'Polygon', 'srid' => 0]);
    expect($first['cities']['polygons_verified'])->toBe(1);
});

test('rejects a checksum mismatch before writing location records', function () {
    $directory = konumFixtureSource();
    config(['konum.source.files.streets.sha256' => str_repeat('0', 64)]);

    expect(fn () => app(KonumImportService::class)->import($directory))->toThrow(RuntimeException::class, 'SHA-256');
    $this->assertDatabaseCount('konum_cities', 0);
});

test('resumes a failed partial import without changing existing source records', function () {
    $directory = konumFixtureSource();
    $this->expectException(RuntimeException::class);
    try {
        app(KonumImportService::class)->import($directory, function (string $table): void {
            if ($table === 'towns') {
                throw new RuntimeException('Simulated interruption');
            }
        });
    } catch (RuntimeException $exception) {
        $this->assertDatabaseCount('konum_cities', 1);
        $this->assertDatabaseCount('konum_neighbourhoods', 0);
        $this->assertDatabaseHas('konum_imports', ['status' => 'failed']);
        app(KonumImportService::class)->import($directory);
        $this->assertDatabaseHas('konum_imports', ['status' => 'complete']);
        $this->assertDatabaseCount('konum_cities', 1);
        throw $exception;
    }
});

test('detects persisted content corruption instead of silently accepting a repeated import', function () {
    $directory = konumFixtureSource();
    app(KonumImportService::class)->import($directory);
    DB::table('konum_streets')->where('id', 1)->update(['name' => 'Changed']);

    expect(fn () => app(KonumImportService::class)->import($directory))->toThrow(RuntimeException::class, 'içerik doğrulaması');
    $this->assertDatabaseHas('konum_imports', ['status' => 'failed']);
    $this->assertDatabaseHas('konum_streets', ['name' => 'Changed']);
});

test('detects inconsistent ancestors even when each parent exists', function () {
    $directory = konumFixtureSource();
    app(KonumImportService::class)->import($directory);
    $city = (array) DB::table('konum_cities')->first();
    $city['id'] = 2;
    $city['source_row_hash'] = KonumImportService::fingerprint($city);
    DB::table('konum_cities')->insert($city);
    $town = (array) DB::table('konum_towns')->first();
    $town['city_id'] = 2;
    $town['source_row_hash'] = KonumImportService::fingerprint($town);
    DB::table('konum_towns')->where('id', 1)->update($town);
    $expected = json_decode(DB::table('konum_imports')->value('report'), true);
    $expected['cities']['source_count'] = 2;
    $expected['cities']['source_digest'] = hash('sha256', DB::table('konum_cities')->where('id', 1)->value('source_row_hash').$city['source_row_hash']);
    $expected['towns']['source_digest'] = hash('sha256', $town['source_row_hash']);

    expect(fn () => app(KonumVerificationService::class)->verify($expected))->toThrow(RuntimeException::class, 'hiyerarşisi');
});

test('rejects invalid polygon bytes without losing the original geometry', function () {
    expect(fn () => app(PolygonGeometry::class)->decode('0000'))->toThrow(RuntimeException::class, 'Polygon');
});

test('preserves inconsistent source fields and quarantines the affected descendants on every import', function () {
    Storage::fake('local');
    $source = konumFixtureSource();
    $directory = Storage::disk('local')->path('inconsistent');
    mkdir($directory, 0755, true);
    foreach (KonumSource::TABLES as $table) {
        $sql = file_get_contents($source.'/'.$table.'.sql');
        if ($table === 'towns') {
            $lines = explode("\n", trim($sql));
            $secondTown = preg_replace('/^\(1,/', '(2,', end($lines));
            $sql = preg_replace('/\);\s*$/', "),\n".$secondTown, $sql);
        }
        if ($table === 'neighbourhoods') {
            $sql = str_replace('(1, 1, 1, 1,', '(1, 1, 2, 1,', $sql);
        }
        file_put_contents($directory.'/'.$table.'.sql', $sql);
        config(['konum.source.files.'.$table => ['bytes' => strlen($sql), 'sha256' => hash('sha256', $sql)]]);
    }

    $report = app(KonumImportService::class)->import($directory);

    expect($report['neighbourhoods'])->toMatchArray(['hierarchy_mismatches' => 1, 'quarantined_count' => 1, 'usable_count' => 0]);
    expect($report['streets']['quarantined_count'])->toBe(1);
    $this->assertDatabaseHas('konum_imports', ['status' => 'warnings']);
    $this->assertDatabaseHas('konum_neighbourhoods', ['id' => 1, 'town_id' => 2, 'is_consistent' => false]);
    expect(app(KonumFrontendService::class)->search('neighbourhoods'))->toBeEmpty();
    expect(app(KonumFrontendService::class)->search('streets'))->toBeEmpty();
    expect(app(KonumImportService::class)->import($directory))->toBe($report);
});

test('prevents overlapping imports of the same database', function () {
    $directory = konumFixtureSource();
    $checked = false;
    app(KonumImportService::class)->import($directory, function () use ($directory, &$checked): void {
        if (! $checked) {
            expect(fn () => app(KonumImportService::class)->import($directory))->toThrow(RuntimeException::class, 'Başka bir Konum importu');
            $checked = true;
        }
    });

    expect($checked)->toBeTrue();
    $this->assertDatabaseHas('konum_imports', ['status' => 'complete']);
});

test('downloads pinned source files and refuses an unexpected response checksum', function () {
    Storage::fake('local');
    Http::preventStrayRequests();
    $directory = konumFixtureSource();
    $url = 'https://raw.githubusercontent.com/'.config('konum.source.repository').'/'.config('konum.source.commit').'/db/';
    $responses = [];
    foreach (KonumSource::TABLES as $table) {
        $responses[$url.$table.'.sql'] = Http::response(file_get_contents($directory.'/'.$table.'.sql'));
    }
    $responses[$url.'cities.sql'] = Http::response('invalid');
    Http::fake($responses);

    expect(fn () => app(KonumSource::class)->prepare(Storage::disk('local')->path('source')))->toThrow(RuntimeException::class, 'SHA-256');
    Http::assertSentCount(1);
    expect(Storage::disk('local')->exists('source/cities.sql'))->toBeFalse();
});
