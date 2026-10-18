<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

test('explicit migration path runs an unregistered disabled modules migrations without running application migrations', function () {
    $disk = Storage::fake('isolated-module-migrations');
    $disk->put('database/migrations/2026_01_01_000000_create_fixture_entries_table.php', <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixture_entries', function (Blueprint $table): void {
            $table->id();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixture_entries');
    }
};
PHP);
    config(['database.connections.module-isolation' => [
        'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
    ]]);

    $this->artisan('migrate', [
        '--path' => $disk->path('database/migrations'), '--realpath' => true, '--force' => true,
        '--database' => 'module-isolation', '--no-interaction' => true,
    ])->assertSuccessful();

    expect(Schema::connection('module-isolation')->hasTable('fixture_entries'))->toBeTrue();
    expect(Schema::connection('module-isolation')->hasTable('admins'))->toBeFalse();
    expect(DB::connection('module-isolation')->table('migrations')->count())->toBe(1);
});
