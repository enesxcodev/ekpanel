<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Models\GalerilerItem;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function galerilerTestImage(string $name): MediaFile
{
    $path = UploadedFile::fake()->image($name)->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => $name, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}

test('galeriler categories form a tree and reject cycles or deletion with children', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->post(route('admin.galeriler.categories.store'), ['name' => 'Ofis', 'icon' => 'photo', 'short_description' => 'Ofis görselleri'])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.galeriler.categories.index');
    $parent = GalerilerCategory::query()->sole();
    $this->post(route('admin.galeriler.categories.store'), ['name' => 'İç mekân', 'parent_id' => $parent->id])
        ->assertSessionHasNoErrors();
    $child = GalerilerCategory::query()->where('parent_id', $parent->id)->sole();

    $this->put(route('admin.galeriler.categories.update', $parent), ['name' => 'Ofis', 'parent_id' => $child->id])
        ->assertSessionHasErrors(['parent_id' => 'Kategori kendisinin veya alt kategorisinin altına taşınamaz.']);
    $this->assertDatabaseHas('galeriler_categories', ['id' => $parent->id, 'parent_id' => null]);
    $this->delete(route('admin.galeriler.categories.destroy', $parent))->assertSessionHasErrors('category');
    $this->get(route('admin.galeriler.categories.index'))->assertOk()->assertSeeInOrder(['Ofis', 'İç mekân']);
    $this->get(route('admin.galeriler.categories.edit', $child))->assertOk();
    $this->delete(route('admin.galeriler.categories.destroy', $child))->assertRedirectToRoute('admin.galeriler.categories.index');
    $this->assertModelMissing($child);
});

test('galeriler creates a separate record and protected usage for every selected photo', function () {
    Storage::fake('public');
    $category = GalerilerCategory::factory()->create();
    $first = galerilerTestImage('first.png');
    $second = galerilerTestImage('second.png');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->post(route('admin.galeriler.store'), [
        'galeriler_category_id' => $category->id, 'type' => 'photo', 'image_media_ids' => json_encode([$first->id, $second->id]),
        'title' => 'Ofis', 'description' => 'İç mekân',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.galeriler.index');

    $this->assertDatabaseCount('galeriler_items', 2);
    $this->assertDatabaseHas('galeriler_items', ['image_media_id' => $first->id, 'galeriler_category_id' => $category->id, 'type' => 'photo', 'sort_order' => 1]);
    $this->assertDatabaseHas('galeriler_items', ['image_media_id' => $second->id, 'sort_order' => 2]);
    $this->assertDatabaseHas('media_usages', ['media_id' => $first->id, 'owner_type' => GalerilerItem::class]);
    $this->delete(route('admin.media.destroy', $first))->assertSessionHasErrors();
    Storage::disk('public')->assertExists($first->path);
});

test('galeriler can change a photo to a video and delete without deleting shared media', function () {
    Storage::fake('public');
    $category = GalerilerCategory::factory()->create();
    $image = galerilerTestImage('shared.png');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->post(route('admin.galeriler.store'), ['galeriler_category_id' => $category->id, 'type' => 'photo', 'image_media_ids' => [$image->id]])->assertSessionHasNoErrors();
    $item = GalerilerItem::query()->sole();
    $this->get(route('admin.galeriler.edit', $item))->assertOk()->assertSee('data-media-multiple="true"', false);

    $this->put(route('admin.galeriler.update', $item), ['galeriler_category_id' => $category->id, 'type' => 'video', 'video_url' => 'https://vimeo.com/123456789'])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.galeriler.index');

    $this->assertDatabaseHas('galeriler_items', ['id' => $item->id, 'type' => 'video', 'image_media_id' => null, 'image' => null]);
    $this->assertDatabaseMissing('media_usages', ['media_id' => $image->id, 'owner_type' => GalerilerItem::class]);
    $this->delete(route('admin.galeriler.destroy', $item))->assertRedirectToRoute('admin.galeriler.index');
    $this->assertModelMissing($item);
    Storage::disk('public')->assertExists($image->path);
});

test('galeriler accepts supported video links and generates a safe preview', function (string $url, string $provider) {
    $category = GalerilerCategory::factory()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.galeriler.store'), ['galeriler_category_id' => $category->id, 'type' => 'video', 'video_url' => $url])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.galeriler.index');

    $item = GalerilerItem::query()->sole();
    expect($item->video['provider'])->toBe($provider);
    expect($item->video['embed_url'])->toStartWith('https://');
    $this->get(route('admin.galeriler.index'))->assertOk();
})->with([
    'youtube' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'youtube'],
    'short link' => ['https://youtu.be/dQw4w9WgXcQ', 'youtube'],
    'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'youtube'],
    'vimeo' => ['https://vimeo.com/123456789', 'vimeo'],
    'private vimeo' => ['https://vimeo.com/123456789/abc123', 'vimeo'],
]);

test('galeriler rejects unsafe or unsupported video URLs without saving', function (string $url) {
    $category = GalerilerCategory::factory()->create();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.galeriler.store'), ['galeriler_category_id' => $category->id, 'type' => 'video', 'video_url' => $url])
        ->assertSessionHasErrors('video_url');
    $this->assertDatabaseCount('galeriler_items', 0);
})->with(['https://youtube.com.evil.test/watch?v=dQw4w9WgXcQ', 'javascript:alert(1)', 'https://www.youtube.com/watch?v=bad', 'http://vimeo.com/123', 'https://youtube.com@evil.test/watch?v=dQw4w9WgXcQ']);

test('galeriler rejects missing photos duplicate selections and nonimage media', function () {
    Storage::fake('public');
    $category = GalerilerCategory::factory()->create();
    $image = galerilerTestImage('one.png');
    $document = MediaFile::factory()->create();
    Storage::disk('public')->put($document->path, 'A document');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $payload = ['galeriler_category_id' => $category->id, 'type' => 'photo'];

    $this->post(route('admin.galeriler.store'), $payload)->assertSessionHasErrors(['image_media_ids' => 'En az bir fotoğraf seçin.']);
    $this->post(route('admin.galeriler.store'), $payload + ['image_media_ids' => [$image->id, $image->id]])->assertSessionHasErrors('image_media_ids.0');
    $this->post(route('admin.galeriler.store'), $payload + ['image_media_ids' => [$document->id]])->assertSessionHasErrors('image_media_ids.0');
    $this->assertDatabaseCount('galeriler_items', 0);
    Storage::disk('public')->assertExists($image->path);
});

test('galeriler rejects malformed video values with validation errors', function (mixed $url) {
    $category = GalerilerCategory::factory()->create();

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->postJson(route('admin.galeriler.store'), ['galeriler_category_id' => $category->id, 'type' => 'video', 'video_url' => $url])
        ->assertUnprocessable()->assertJsonValidationErrors('video_url');

    $this->assertDatabaseCount('galeriler_items', 0);
})->with(['array' => [['https://vimeo.com/123']], 'number' => [123]]);

test('galeriler partial reordering preserves records outside the displayed selection', function () {
    $category = GalerilerCategory::factory()->create();
    $first = GalerilerItem::factory()->create(['galeriler_category_id' => $category->id, 'sort_order' => 1]);
    $second = GalerilerItem::factory()->create(['galeriler_category_id' => $category->id, 'sort_order' => 2]);
    $third = GalerilerItem::factory()->create(['galeriler_category_id' => $category->id, 'sort_order' => 3]);

    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->putJson(route('admin.galeriler.reorder'), ['category_id' => $category->id, 'ids' => [$third->id, $second->id]])->assertOk();

    expect($category->items()->pluck('id')->all())->toBe([$first->id, $third->id, $second->id]);
});

test('galeriler filters cards by dynamic category tabs type and literal search and escapes text', function () {
    $first = GalerilerCategory::factory()->create(['name' => 'Ofis']);
    $second = GalerilerCategory::factory()->create(['name' => 'Tesis']);
    GalerilerItem::factory()->create(['galeriler_category_id' => $first->id, 'title' => '<script>Ofis</script>', 'description' => 'Özel % alan']);
    GalerilerItem::factory()->create(['galeriler_category_id' => $second->id, 'title' => 'Başka kayıt']);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->get(route('admin.galeriler.index', ['category_id' => $first->id]))
        ->assertOk()->assertSee('Galeriler kategorileri')->assertSee('&lt;script&gt;Ofis&lt;/script&gt;', false)->assertDontSee('<script>Ofis</script>', false)
        ->assertDontSee('Başka kayıt')->assertSee('data-galeriler-reorder', false);
    $this->get(route('admin.galeriler.index', ['category_id' => $first->id, 'search' => '%']))
        ->assertOk()->assertSee('&lt;script&gt;Ofis&lt;/script&gt;', false)->assertDontSee('data-galeriler-reorder=', false);
    $this->get(route('admin.galeriler.index', ['category_id' => $first->id, 'type' => 'photo']))->assertOk()->assertDontSee('&lt;script&gt;Ofis&lt;/script&gt;', false);
});

test('galeriler bulk move and delete affect only selected records', function () {
    $source = GalerilerCategory::factory()->create();
    $target = GalerilerCategory::factory()->create();
    $selected = GalerilerItem::factory()->create(['galeriler_category_id' => $source->id]);
    $remaining = GalerilerItem::factory()->create(['galeriler_category_id' => $source->id]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->post(route('admin.galeriler.bulk'), ['action' => 'move', 'ids' => [$selected->id], 'galeriler_category_id' => $target->id])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('galeriler_items', ['id' => $selected->id, 'galeriler_category_id' => $target->id]);
    $this->assertDatabaseHas('galeriler_items', ['id' => $remaining->id, 'galeriler_category_id' => $source->id]);
    $this->post(route('admin.galeriler.bulk'), ['action' => 'delete', 'ids' => [$selected->id]])->assertRedirectToRoute('admin.galeriler.index');
    $this->assertModelMissing($selected);
    $this->assertModelExists($remaining);
});

test('galeriler reorders within a category and rejects other categories or filtered ordering', function () {
    $category = GalerilerCategory::factory()->create();
    $first = GalerilerItem::factory()->create(['galeriler_category_id' => $category->id, 'sort_order' => 1]);
    $second = GalerilerItem::factory()->create(['galeriler_category_id' => $category->id, 'sort_order' => 2]);
    $other = GalerilerItem::factory()->create(['sort_order' => 9]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->putJson(route('admin.galeriler.reorder'), ['category_id' => $category->id, 'ids' => [$second->id, $first->id]])->assertOk();
    expect($category->items()->pluck('id')->all())->toBe([$second->id, $first->id]);
    $this->putJson(route('admin.galeriler.reorder'), ['category_id' => $category->id, 'ids' => [$other->id]])->assertUnprocessable()->assertJsonValidationErrors('ids');
    $this->putJson(route('admin.galeriler.reorder'), ['category_id' => $category->id, 'ids' => [$first->id], 'search' => 'test'])->assertUnprocessable();
    $this->putJson(route('admin.galeriler.reorder'), ['category_id' => $category->id, 'ids' => [$first->id], 'type' => 'photo'])->assertUnprocessable();
    $this->assertDatabaseHas('galeriler_items', ['id' => $other->id, 'sort_order' => 9]);
});

test('galeriler requires authentication permissions and enabled module even for super admin', function () {
    $this->get(route('admin.galeriler.index'))->assertRedirectToRoute('admin.login');
    $this->actingAs(Admin::factory()->create(), 'admin')->get(route('admin.galeriler.index'))->assertForbidden();
    $this->post(route('admin.galeriler.bulk'), ['action' => 'delete', 'ids' => [1]])->assertForbidden();
    $this->putJson(route('admin.galeriler.reorder'), ['category_id' => 1, 'ids' => [1]])->assertForbidden();

    $disk = Storage::fake('disabled-galeriler');
    $disk->put('statuses.json', json_encode(['Galeriler' => false, 'Projects' => true, 'Testimonials' => true]));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->get(route('admin.galeriler.index'))->assertNotFound();
});

test('galeriler view permission does not grant writes or media selection', function () {
    Storage::fake('public');
    app(ModulePanelService::class)->syncPermissions();
    $category = GalerilerCategory::factory()->create();
    $image = galerilerTestImage('restricted.png');
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['galeriler.view', 'galeriler.create']);
    $this->actingAs($admin, 'admin')->get(route('admin.galeriler.create'))->assertOk()->assertSee('data-media-disabled="true"', false);
    $this->post(route('admin.galeriler.store'), ['galeriler_category_id' => $category->id, 'type' => 'photo', 'image_media_ids' => [$image->id]])->assertForbidden();
    $this->assertDatabaseCount('galeriler_items', 0);
    Storage::disk('public')->assertExists($image->path);
});
