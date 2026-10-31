<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\Language;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Team\Models\TeamMember;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

function teamTestLanguageId(): int
{
    return Language::query()->where('code', 'tr')->valueOrFail('id');
}

function teamTestEditUrl(?TeamMember $member = null): string
{
    return route('admin.team.edit', ['member' => $member?->id ?? TeamMember::query()->latest('id')->valueOrFail('id'), 'lang' => 'tr']);
}

function teamTestMember(array $translation): TeamMember
{
    $member = TeamMember::factory()->create();
    $member->translations()->where('lang_id', teamTestLanguageId())->firstOrFail()->forceFill($translation)->save();

    return $member->fresh();
}

function teamTestImage(string $name, bool $oversized = false): MediaFile
{
    $upload = UploadedFile::fake()->image($name);
    $path = $upload->store('_media', 'public');
    if ($oversized) {
        $contents = Storage::disk('public')->get($path);
        Storage::disk('public')->put($path, $contents.str_repeat("\0", max(0, 4097 * 1024 - strlen($contents))));
    }

    return MediaFile::factory()->create(['path' => $path, 'name' => $name, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}

function teamTestDocument(): MediaFile
{
    $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');
    $path = $upload->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => 'document.pdf', 'kind' => 'document', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => Storage::disk('public')->size($path)]);
}

test('team has a single sidebar link with its table and create button and shared forms', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $member = teamTestMember(['name' => '<script>name</script>', 'title' => '<script>title</script>']);
    $member->update(['is_active' => false]);

    $index = $this->get(route('admin.team.index'))->assertOk()
        ->assertSee('id="team-members-table"', false)->assertSee('Ekip ekle')
        ->assertSee('href="'.route('admin.team.create').'"', false);
    expect(substr_count($index->getContent(), 'href="'.route('admin.team.index').'"'))->toBe(1);
    $this->get(route('admin.team.create'))->assertOk()
        ->assertSeeInOrder(['name="image_media_id"', 'name="is_active"', 'name="name"', 'name="title"'], false);
    $this->get(route('admin.team.edit', $member))->assertOk()
        ->assertSee('&lt;script&gt;name&lt;/script&gt;', false)->assertSee('&lt;script&gt;title&lt;/script&gt;', false)
        ->assertDontSee('<script>name</script>', false)->assertDontSee('<script>title</script>', false)
        ->assertSee('name="is_active"', false);
});

test('team can save an inactive member without a title or image and activate it later', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->post(route('admin.team.store'), ['lang_id' => teamTestLanguageId(), 'name' => 'Ayşe Yılmaz', 'is_active' => '0'])
        ->assertSessionHasNoErrors()->assertRedirect(teamTestEditUrl())
        ->assertSessionHas('notification.message', 'Ekip üyesi başarıyla eklendi.');
    $member = TeamMember::query()->sole();
    expect($member->is_active)->toBeFalse();
    expect($member->image)->toBeNull();
    expect($member->title)->toBeNull();
    $this->assertDatabaseHas('team_members', ['id' => $member->id, 'is_active' => false]);
    $this->assertDatabaseHas('team_member_translations', ['team_member_id' => $member->id, 'lang_id' => teamTestLanguageId(), 'name' => 'Ayşe Yılmaz', 'title' => null]);

    $this->put(route('admin.team.update', $member), ['lang_id' => teamTestLanguageId(), 'name' => 'Ayşe Yılmaz', 'title' => 'Proje Yöneticisi', 'is_active' => '1'])
        ->assertSessionHasNoErrors()->assertRedirect(teamTestEditUrl($member));
    $this->assertDatabaseHas('team_members', ['id' => $member->id, 'is_active' => true]);
    $this->assertDatabaseHas('team_member_translations', ['team_member_id' => $member->id, 'lang_id' => teamTestLanguageId(), 'title' => 'Proje Yöneticisi']);
});

test('team selects preserves replaces and detaches the member image', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $firstMedia = teamTestImage('first.png');
    $this->post(route('admin.team.store'), ['lang_id' => teamTestLanguageId(), 'name' => 'Ali Yılmaz', 'is_active' => '1', 'image_media_id' => $firstMedia->id])
        ->assertSessionHasNoErrors()->assertRedirect(teamTestEditUrl());
    $member = TeamMember::query()->sole();
    $firstImage = $member->image;
    Storage::disk('public')->assertExists($firstImage);

    $this->put(route('admin.team.update', $member), ['lang_id' => teamTestLanguageId(), 'name' => 'Ali Yılmaz', 'is_active' => '0'])->assertSessionHasNoErrors();
    expect($member->fresh()->image)->toBe($firstImage);
    Storage::disk('public')->assertExists($firstImage);

    $secondMedia = teamTestImage('second.png');
    $this->put(route('admin.team.update', $member), ['lang_id' => teamTestLanguageId(), 'name' => 'Ali Yılmaz', 'is_active' => '1', 'image_media_id' => $secondMedia->id])
        ->assertSessionHasNoErrors();
    $secondImage = $member->fresh()->image;
    Storage::disk('public')->assertExists($secondImage);
    Storage::disk('public')->assertExists($firstImage);

    $this->delete(route('admin.team.destroy', $member))->assertRedirectToRoute('admin.team.index')
        ->assertSessionHas('notification.message', 'Ekip üyesi başarıyla silindi.');
    $this->assertModelMissing($member);
    Storage::disk('public')->assertExists($secondImage);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => TeamMember::class, 'owner_id' => $member->id]);
});

test('team removes an image reference and rejects simultaneous replacement and removal', function () {
    Storage::fake('public');
    Storage::disk('public')->put('team/images/existing.png', 'existing image');
    $member = TeamMember::factory()->create(['image' => 'team/images/existing.png']);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $replacement = teamTestImage('replacement.png');
    $this->put(route('admin.team.update', $member), ['lang_id' => teamTestLanguageId(), 'name' => 'Yeni isim', 'is_active' => '0', 'remove_image' => '1', 'image_media_id' => $replacement->id])
        ->assertSessionHasErrors('image_media_id');
    Storage::disk('public')->assertExists('team/images/existing.png');
    Storage::disk('public')->assertCount('team/images', 1);
    expect($member->fresh()->image)->toBe('team/images/existing.png');

    $this->put(route('admin.team.update', $member), ['lang_id' => teamTestLanguageId(), 'name' => 'Yeni isim', 'is_active' => '0', 'remove_image' => '1'])
        ->assertSessionHasNoErrors()->assertRedirect(teamTestEditUrl($member));
    expect($member->fresh()->image)->toBeNull();
    Storage::disk('public')->assertExists('team/images/existing.png');
});

test('team requires the member name and an explicit active state', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.team.store'), [])
        ->assertSessionHasErrors(['name' => 'İsim alanını doldurun.', 'is_active' => 'Durum alanını doldurun.']);

    $this->assertDatabaseCount('team_members', 0);
});

test('team rejects invalid input without saving a member or its image', function (string $field, mixed $value, string $message) {
    Storage::fake('public');
    $payload = ['lang_id' => teamTestLanguageId(), 'name' => 'Yeni ekip üyesi', 'is_active' => '1', 'image_media_id' => teamTestImage('valid.png')->id];
    data_set($payload, $field, $value);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->post(route('admin.team.store'), $payload)
        ->assertSessionHasErrors([$field => $message]);

    $this->assertDatabaseCount('team_members', 0);
    Storage::disk('public')->assertCount('_media', 1);
})->with([
    'name is not text' => ['name', ['invalid'], 'İsim metin olmalıdır.'],
    'name is too long' => ['name', str_repeat('a', 256), 'İsim en fazla 255 karakter olabilir.'],
    'title is too long' => ['title', str_repeat('a', 256), 'Ünvan en fazla 255 karakter olabilir.'],
    'invalid active state' => ['is_active', 'enabled', 'Durum geçerli bir açık / kapalı değeri olmalıdır.'],
    'invalid image removal' => ['remove_image', 'remove', 'Görseli kaldır geçerli bir açık / kapalı değeri olmalıdır.'],
    'invalid media selection' => ['image_media_id', 999999, 'Kütüphaneden aktif ve geçerli bir dosya seçin.'],
]);

test('team rejects non images and oversized uploads', function (bool $oversized) {
    Storage::fake('public');
    $image = $oversized ? teamTestImage('large.png', true) : teamTestDocument();

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.team.store'), ['lang_id' => teamTestLanguageId(), 'name' => 'Yeni ekip üyesi', 'is_active' => '1', 'image_media_id' => $image->id])
        ->assertSessionHasErrors('image_media_id');

    $this->assertDatabaseCount('team_members', 0);
    Storage::disk('public')->assertCount('_media', 1);
})->with([true, false]);

test('team connects configured extra fields to its forms validation and persisted values', function () {
    $disk = Storage::fake('team-feature-definitions');
    $disk->put('Team/module.json', json_encode(['name' => 'Team', 'providers' => []], JSON_THROW_ON_ERROR));
    $disk->put('Team/features.json', json_encode(['schema_version' => 1, 'forms' => ['member' => ['label' => 'Ek ekip alanları', 'fields' => [
        ['key' => 'biography', 'label' => 'Biyografi', 'input_type' => 'textarea', 'value_type' => 'string', 'validation' => ['max' => 50]],
    ]]]], JSON_THROW_ON_ERROR));
    $disk->put('statuses.json', json_encode(['Team' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.team.create'))->assertOk()->assertSee('name="extra_fields[biography]"', false);
    $this->post(route('admin.team.store'), ['lang_id' => teamTestLanguageId(), 'name' => 'Ekip üyesi', 'is_active' => '1', 'extra_fields' => ['biography' => '<script>Metin</script>']])
        ->assertSessionHasNoErrors()->assertRedirect(teamTestEditUrl());
    $member = TeamMember::query()->sole();
    expect($member->translations()->firstOrFail()->extra_fields)->toBe(['biography' => '<script>Metin</script>']);
    $this->get(route('admin.team.edit', $member))->assertOk()->assertSee('&lt;script&gt;Metin&lt;/script&gt;', false);
    $this->put(route('admin.team.update', $member), ['lang_id' => teamTestLanguageId(), 'name' => 'Ekip üyesi', 'is_active' => '1', 'extra_fields' => ['biography' => str_repeat('a', 51)]])
        ->assertSessionHasErrors(['extra_fields.biography' => 'Biyografi en fazla 50 karakter olabilir.']);
    expect($member->translations()->firstOrFail()->extra_fields)->toBe(['biography' => '<script>Metin</script>']);
});

test('team rejects undefined extra fields', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.team.store'), ['lang_id' => teamTestLanguageId(), 'name' => 'Ekip üyesi', 'is_active' => '1', 'extra_fields' => ['unknown' => 'injected']])
        ->assertSessionHasErrors('extra_fields');

    $this->assertDatabaseCount('team_members', 0);
});

test('team edits the selected language without replacing the Turkish translation', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $member = teamTestMember(['name' => 'Türkçe QA', 'title' => 'Türkçe Ünvan']);
    $english = Language::query()->firstOrCreate(['code' => 'en'], ['is_active' => true]);
    $english->forceFill(['is_active' => true])->save();

    $this->get(route('admin.team.edit', ['member' => $member, 'lang' => 'en']))->assertOk()
        ->assertSee('Çeviri yok')->assertSee('name="name"', false);
    $this->put(route('admin.team.update', $member), [
        'lang_id' => $english->id, 'name' => 'English QA', 'title' => 'English Title',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.team.edit', ['member' => $member->id, 'lang' => 'en']));

    $this->assertDatabaseHas('team_member_translations', ['team_member_id' => $member->id, 'lang_id' => teamTestLanguageId(), 'name' => 'Türkçe QA', 'title' => 'Türkçe Ünvan']);
    $this->assertDatabaseHas('team_member_translations', ['team_member_id' => $member->id, 'lang_id' => $english->id, 'name' => 'English QA', 'title' => 'English Title']);
});

test('team endpoints enforce their operation permissions', function (string $method, string $action, string $permission, bool $granted) {
    Storage::fake('public');
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    if ($granted) {
        $admin->givePermissionTo($permission);
    }
    $member = teamTestMember(['name' => 'Original']);
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $member : [];

    $response = $this->actingAs($admin, 'admin')->{$method}(route('admin.team.'.$action, $parameters), [
        'lang_id' => teamTestLanguageId(), 'name' => 'Updated', 'is_active' => '0',
    ]);

    if (! $granted) {
        $response->assertForbidden();
        expect($member->fresh()->name)->toBe('Original');
        $this->assertDatabaseCount('team_members', 1);
    } elseif ($action === 'destroy') {
        $response->assertRedirectToRoute('admin.team.index');
        $this->assertModelMissing($member);
    } elseif (in_array($action, ['store', 'update'], true)) {
        $response->assertRedirect(teamTestEditUrl($action === 'update' ? $member : null));
        $savedMember = $action === 'store' ? TeamMember::query()->latest('id')->firstOrFail() : $member;
        $this->assertDatabaseHas('team_member_translations', ['team_member_id' => $savedMember->id, 'lang_id' => teamTestLanguageId(), 'name' => 'Updated']);
        $this->assertDatabaseHas('team_members', ['id' => $savedMember->id, 'is_active' => false]);
    } else {
        $response->assertOk();
    }
    Storage::disk('public')->assertEmpty();
})->with([
    'index' => ['get', 'index', 'team.view'],
    'data' => ['get', 'data', 'team.view'],
    'create' => ['get', 'create', 'team.create'],
    'store' => ['post', 'store', 'team.create'],
    'edit' => ['get', 'edit', 'team.update'],
    'update' => ['put', 'update', 'team.update'],
    'destroy' => ['delete', 'destroy', 'team.delete'],
])->with([true, false]);

test('team menu and page permissions remain independent', function (array $permissions, bool $visible, bool $accessible) {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);
    $this->actingAs($admin, 'admin');

    $dashboard = $this->get(route('admin.dashboard'))->assertOk();
    if ($visible) {
        $dashboard->assertSee('href="'.route('admin.team.index').'"', false);
    } else {
        $dashboard->assertDontSee('href="'.route('admin.team.index').'"', false);
    }
    $index = $this->get(route('admin.team.index'));
    if ($accessible) {
        $index->assertOk()->assertDontSee('href="'.route('admin.team.create').'"', false);
    } else {
        $index->assertForbidden();
    }
})->with([
    'menu alone' => [['team.menu'], false, false],
    'page alone' => [['team.view'], false, true],
    'menu and page' => [['team.menu', 'team.view'], true, true],
]);

test('team blocks guests and inactive accounts', function () {
    $this->get(route('admin.team.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(Admin::factory()->superAdmin()->inactive()->create(), 'admin')
        ->get(route('admin.team.index'))->assertRedirectToRoute('admin.login');
});

test('disabled team module is hidden and all endpoints reject even super admins', function (string $method, string $action) {
    $disk = Storage::fake('disabled-team');
    $disk->put('statuses.json', json_encode(['Services' => true, 'Slider' => true, 'Team' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $member = TeamMember::factory()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $member : [];

    $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('href="'.route('admin.team.index').'"', false);
    $this->{$method}(route('admin.team.'.$action, $parameters), ['lang_id' => teamTestLanguageId(), 'name' => 'Forbidden', 'is_active' => '1'])->assertNotFound();

    $this->assertModelExists($member);
    $this->assertDatabaseCount('team_members', 1);
})->with([
    ['get', 'index'], ['get', 'data'], ['get', 'create'], ['post', 'store'],
    ['get', 'edit'], ['put', 'update'], ['delete', 'destroy'],
]);

test('team data table escapes user text displays both statuses and only offers permitted actions', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['team.view', 'team.update']);
    teamTestMember(['name' => '<script>name</script>', 'title' => '<script>title</script>']);
    $inactive = teamTestMember(['name' => 'Pasif üye']);
    $inactive->update(['is_active' => false]);

    $this->actingAs($admin, 'admin')->getJson(route('admin.team.data'))
        ->assertOk()->assertJsonPath('recordsTotal', 2)
        ->assertJsonPath('data.0.name', '&lt;script&gt;name&lt;/script&gt;')
        ->assertJsonPath('data.0.title', '&lt;script&gt;title&lt;/script&gt;')
        ->assertJsonPath('data.0.is_active', fn (string $status): bool => str_contains($status, 'Aktif'))
        ->assertJsonPath('data.1.is_active', fn (string $status): bool => str_contains($status, 'Pasif'))
        ->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, 'Düzenle') && ! str_contains($actions, '>Sil<'));
});
