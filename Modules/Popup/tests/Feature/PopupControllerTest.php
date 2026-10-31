<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\Language;
use App\Services\Admin\Modules\ModulePanelService;
use Database\Seeders\MediaPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Popup\Models\Popup;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

/** @return array<string, mixed> */
function popupPayload(): array
{
    return ['lang_id' => Language::query()->where('code', 'tr')->value('id'), 'title' => 'Duyuru', 'content' => '<p>Duyuru içeriği</p>', 'starts_at' => '2026-10-19T10:00', 'ends_at' => '2026-10-20T10:00', 'repeat_policy' => 'never_after_close', 'is_active' => true];
}

function popupImage(): MediaFile
{
    $path = UploadedFile::fake()->image('popup.png')->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => 'popup.png', 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}

test('popup has separate create edit and listing pages with shared form fields', function () {
    $popup = Popup::factory()->create();
    $popup->translations()->first()->update(['title' => '<script>bad</script>']);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.popup.index'))->assertOk()->assertSee(route('admin.popup.create'));
    $this->get(route('admin.popup.create'))->assertOk()->assertSeeInOrder(['name="title"', 'name="content"', 'name="image_media_id"', 'name="starts_at"', 'name="ends_at"', 'name="repeat_policy"'], false);
    $this->get(route('admin.popup.edit', ['popup' => $popup, 'lang' => 'tr']))->assertOk()->assertSee('&lt;script&gt;bad&lt;/script&gt;', false)->assertDontSee('<script>bad</script>', false);
});

test('popup CRUD sanitizes content manages library usage and resets unused repeat settings', function () {
    Storage::fake('public');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $image = popupImage();
    $response = $this->post(route('admin.popup.store'), [...popupPayload(), 'content' => '<p>Merhaba</p><script>alert(1)</script>', 'image_media_id' => $image->id, 'repeat_policy' => 'interval', 'repeat_interval' => 2, 'repeat_unit' => 'days'])
        ->assertSessionHasNoErrors();
    $popup = Popup::query()->sole();
    $response->assertRedirectToRoute('admin.popup.edit', ['popup' => $popup->id, 'lang' => 'tr']);
    expect($popup->translations()->where('lang_id', popupPayload()['lang_id'])->sole()->content)->toContain('<p>Merhaba</p>')->not->toContain('<script>');
    expect($popup->repeat_interval)->toBe(2);
    $this->assertDatabaseHas('media_usages', ['owner_type' => Popup::class, 'owner_id' => $popup->id, 'media_id' => $image->id, 'field' => 'image']);
    $this->put(route('admin.popup.update', $popup), [...popupPayload(), 'image_media_id' => null, 'is_active' => false, 'repeat_interval' => 99, 'repeat_unit' => 'hours'])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.popup.edit', ['popup' => $popup->id, 'lang' => 'tr']);
    expect($popup->fresh())->is_active->toBeFalse()->image_media_id->toBeNull()->repeat_interval->toBeNull()->repeat_unit->toBeNull();
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Popup::class, 'owner_id' => $popup->id]);
    $this->delete(route('admin.popup.destroy', $popup))->assertRedirectToRoute('admin.popup.index');
    $this->assertModelMissing($popup);
    Storage::disk('public')->assertExists($image->path);
    $this->assertDatabaseCount('notifications', 0);
});

test('popup rejects invalid fields without creating records', function (array $overrides, string $field) {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.popup.store'), [...popupPayload(), ...$overrides])->assertSessionHasErrors($field);
    $this->assertDatabaseCount('popups', 0);
    $this->assertDatabaseCount('notifications', 0);
})->with([
    'missing title' => [['title' => ''], 'title'],
    'missing content' => [['content' => ''], 'content'],
    'invalid start' => [['starts_at' => 'invalid'], 'starts_at'],
    'equal end' => [['ends_at' => '2026-10-19T10:00'], 'ends_at'],
    'earlier end' => [['ends_at' => '2026-10-18T10:00'], 'ends_at'],
    'unknown policy' => [['repeat_policy' => 'always'], 'repeat_policy'],
    'missing interval' => [['repeat_policy' => 'interval', 'repeat_unit' => 'hours'], 'repeat_interval'],
    'zero interval' => [['repeat_policy' => 'interval', 'repeat_interval' => 0, 'repeat_unit' => 'hours'], 'repeat_interval'],
    'fraction interval' => [['repeat_policy' => 'interval', 'repeat_interval' => 1.5, 'repeat_unit' => 'hours'], 'repeat_interval'],
    'invalid unit' => [['repeat_policy' => 'interval', 'repeat_interval' => 1, 'repeat_unit' => 'minutes'], 'repeat_unit'],
    'invalid media' => [['image_media_id' => 999999], 'image_media_id'],
    'raw image' => [['image' => 'arbitrary.png'], 'image'],
    'unknown extra field' => [['extra_fields' => ['injected' => 'value']], 'extra_fields'],
    'invalid state' => [['is_active' => 'yes'], 'is_active'],
]);

test('popup endpoints enforce operation permissions', function (string $method, string $action, string $permission) {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $popup = Popup::factory()->create();
    $popup->translations()->first()->update(['title' => 'Original']);
    $parameters = in_array($action, ['edit', 'update', 'destroy'], true) ? $popup : [];
    $this->actingAs($admin, 'admin')->{$method}(route('admin.popup.'.$action, $parameters), popupPayload())->assertForbidden();
    expect($popup->translations()->where('lang_id', popupPayload()['lang_id'])->sole()->title)->toBe('Original');
    $admin->givePermissionTo($permission);
    $response = $this->actingAs($admin, 'admin')->{$method}(route('admin.popup.'.$action, $parameters), popupPayload());
    if (in_array($action, ['store', 'update', 'destroy'], true)) {
        $response->assertSessionHasNoErrors();
    } else {
        $response->assertOk();
    }
})->with([
    ['get', 'index', 'popup.view'], ['get', 'data', 'popup.view'],
    ['get', 'create', 'popup.create'], ['post', 'store', 'popup.create'],
    ['get', 'edit', 'popup.update'], ['put', 'update', 'popup.update'],
    ['delete', 'destroy', 'popup.delete'],
]);

test('popup notifies all admins after customer CRUD with saved record links', function () {
    app(ModulePanelService::class)->syncPermissions();
    $actor = Admin::factory()->create();
    $recipient = Admin::factory()->superAdmin()->create();
    $actor->givePermissionTo(['popup.create', 'popup.update', 'popup.delete']);
    $this->actingAs($actor, 'admin')->post(route('admin.popup.store'), popupPayload())->assertSessionHasNoErrors();
    $popup = Popup::query()->sole();
    foreach ([$actor, $recipient] as $admin) {
        expect($admin->notifications()->sole()->data)->toMatchArray(['title' => 'Pop-up eklendi', 'link' => route('admin.popup.edit', ['popup' => $popup, 'lang' => 'tr'], absolute: false)]);
    }
    $this->put(route('admin.popup.update', $popup), [...popupPayload(), 'title' => 'Yeni'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('popup_translations', ['popup_id' => $popup->id, 'title' => 'Yeni']);
    expect($recipient->notifications()->where('data->title', 'Pop-up güncellendi')->sole()->data['link'])->toBe(route('admin.popup.edit', ['popup' => $popup, 'lang' => 'tr'], absolute: false));
    $this->delete(route('admin.popup.destroy', $popup))->assertRedirectToRoute('admin.popup.index');
    expect($recipient->notifications()->where('data->title', 'Pop-up silindi')->sole()->data['link'])->toBe(route('admin.popup.index', absolute: false));
    $this->assertDatabaseCount('notifications', 6);
    $this->assertModelMissing($popup);
});

test('popup media changes require library permission and reject documents', function () {
    Storage::fake('public');
    app(ModulePanelService::class)->syncPermissions();
    $this->seed(MediaPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('popup.create');
    $image = popupImage();
    $this->actingAs($admin, 'admin')->post(route('admin.popup.store'), [...popupPayload(), 'image_media_id' => $image->id])->assertForbidden();
    $admin->givePermissionTo('media.view');
    $document = MediaFile::factory()->create(['kind' => 'document']);
    $this->post(route('admin.popup.store'), [...popupPayload(), 'image_media_id' => $document->id])->assertSessionHasErrors('image_media_id');
    $this->assertDatabaseCount('popups', 0);
});

test('popup blocks guests inactive accounts and disabled module access', function () {
    $this->get(route('admin.popup.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(Admin::factory()->superAdmin()->inactive()->create(), 'admin')->get(route('admin.popup.index'))->assertRedirectToRoute('admin.login');
    $disk = Storage::fake('disabled-popup');
    $disk->put('statuses.json', json_encode(['Popup' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->get(route('admin.popup.index'))->assertNotFound();
    $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('href="'.route('admin.popup.index').'"', false);
});

test('popup data table escapes titles and limits action links to granted permissions', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['popup.view', 'popup.update']);
    $popup = Popup::factory()->create();
    $popup->translations()->first()->update(['title' => '<script>alert(1)</script>']);
    $this->actingAs($admin, 'admin')->getJson(route('admin.popup.data'))->assertOk()->assertJsonPath('recordsTotal', 1)
        ->assertJsonPath('data.0.title', '&lt;script&gt;alert(1)&lt;/script&gt;')
        ->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, 'Düzenle') && ! str_contains($actions, '>Sil<'));
});

test('popup extra fields render validate persist and survive omitted update values', function () {
    $disk = Storage::fake('popup-feature-definitions');
    $disk->put('Popup/module.json', file_get_contents(base_path('Modules/Popup/module.json')));
    $disk->put('Popup/features.json', json_encode(['schema_version' => 1, 'forms' => ['popup' => ['label' => 'Ek alanlar', 'fields' => [
        ['key' => 'button_text', 'label' => 'Buton metni', 'input_type' => 'text', 'value_type' => 'string', 'validation' => ['required' => false, 'max' => 20]],
    ]]]], JSON_THROW_ON_ERROR));
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.popup.create'))->assertOk()->assertSee('name="extra_fields[button_text]"', false);
    $this->post(route('admin.popup.store'), [...popupPayload(), 'extra_fields' => ['button_text' => str_repeat('a', 21)]])->assertSessionHasErrors('extra_fields.button_text');
    $this->assertDatabaseCount('popups', 0);
    $this->post(route('admin.popup.store'), [...popupPayload(), 'extra_fields' => ['button_text' => '<b>İncele</b>']])->assertSessionHasNoErrors();
    $popup = Popup::query()->sole();
    expect($popup->translations()->where('lang_id', popupPayload()['lang_id'])->sole()->extra_fields)->toBe(['button_text' => '<b>İncele</b>']);
    $this->get(route('admin.popup.edit', ['popup' => $popup, 'lang' => 'tr']))->assertOk()->assertSee('&lt;b&gt;İncele&lt;/b&gt;', false);
    $this->put(route('admin.popup.update', $popup), popupPayload())->assertSessionHasNoErrors();
    expect($popup->translations()->where('lang_id', popupPayload()['lang_id'])->sole()->extra_fields)->toBe(['button_text' => '<b>İncele</b>']);
});

test('popup translations stay separate and inactive languages cannot be saved', function () {
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.popup.store'), [...popupPayload(), 'title' => 'Türkçe başlık'])->assertSessionHasNoErrors();
    $popup = Popup::query()->sole();
    $this->put(route('admin.popup.update', ['popup' => $popup, 'lang' => 'en']), [...popupPayload(), 'lang_id' => $english->id, 'title' => 'English title', 'content' => '<p>English content</p>'])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.popup.edit', ['popup' => $popup->id, 'lang' => 'en']);

    $this->assertDatabaseHas('popup_translations', ['popup_id' => $popup->id, 'lang_id' => popupPayload()['lang_id'], 'title' => 'Türkçe başlık']);
    $this->assertDatabaseHas('popup_translations', ['popup_id' => $popup->id, 'lang_id' => $english->id, 'title' => 'English title']);
    $this->get(route('admin.popup.edit', ['popup' => $popup, 'lang' => 'en']))->assertOk()->assertSee('English title')->assertDontSee('Türkçe başlık');

    $english->update(['is_active' => false]);
    $this->put(route('admin.popup.update', $popup), [...popupPayload(), 'lang_id' => $english->id, 'title' => 'Rejected'])->assertSessionHasErrors('lang_id');
});

test('popup sidebar requires both menu and view permissions', function (array $permissions, bool $visible) {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);
    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();
    $link = 'href="'.route('admin.popup.index').'"';
    if ($visible) {
        $response->assertSee($link, false);
    } else {
        $response->assertDontSee($link, false);
    }
})->with([
    'menu only' => [['popup.menu'], false],
    'view only' => [['popup.view'], false],
    'both permissions' => [['popup.menu', 'popup.view'], true],
]);
