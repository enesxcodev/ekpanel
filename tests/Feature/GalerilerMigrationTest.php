<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Media\MediaUsage;
use App\Services\Media\MediaService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Models\GalerilerItem;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(LazilyRefreshDatabase::class);

test('galeriler migration preserves content media references and existing permission assignments', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->image('shared.png')->store('_media', 'public');
    $media = MediaFile::factory()->create(['path' => $path]);
    $category = GalerilerCategory::factory()->create();
    $child = GalerilerCategory::factory()->create(['parent_id' => $category->id]);
    $photo = GalerilerItem::factory()->create(['galeriler_category_id' => $child->id, 'type' => 'photo', 'image_media_id' => $media->id, 'image' => $path, 'video_url' => null, 'sort_order' => 7]);
    $video = GalerilerItem::factory()->create(['galeriler_category_id' => $category->id, 'sort_order' => 8]);
    MediaUsage::query()->create(['owner_type' => GalerilerItem::class, 'owner_id' => $photo->id, 'media_id' => $media->id, 'field' => 'image', 'label' => 'Fotoğraf', 'route_name' => 'admin.galeriler.edit']);
    $admin = Admin::factory()->create();
    $legacyPermission = Permission::findOrCreate('gallery.view', 'admin');
    $admin->givePermissionTo($legacyPermission);
    $role = Role::findOrCreate('content-editor', 'admin');
    $role->givePermissionTo($legacyPermission);
    Permission::findOrCreate('galeriler.view', 'admin');
    $migration = require base_path('Modules/Galeriler/database/migrations/2026_10_17_224948_rename_content_gallery_tables_to_galeriler.php');

    $migration->down();
    $this->assertDatabaseHas('gallery_items', ['id' => $photo->id, 'gallery_category_id' => $child->id, 'image_media_id' => $media->id, 'sort_order' => 7]);
    $migration->up();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->assertDatabaseHas('galeriler_categories', ['id' => $child->id, 'parent_id' => $category->id]);
    $this->assertDatabaseHas('galeriler_items', ['id' => $photo->id, 'galeriler_category_id' => $child->id, 'image_media_id' => $media->id, 'sort_order' => 7]);
    $this->assertDatabaseHas('galeriler_items', ['id' => $video->id, 'type' => 'video', 'sort_order' => 8]);
    $this->assertDatabaseHas('media_usages', ['owner_type' => GalerilerItem::class, 'owner_id' => $photo->id, 'route_name' => 'admin.galeriler.edit']);
    $this->assertDatabaseHas('permissions', ['id' => $legacyPermission->id, 'name' => 'gallery.view', 'guard_name' => 'admin']);
    expect($admin->fresh()->hasPermissionTo('galeriler.view', 'admin'))->toBeTrue();
    expect($role->fresh()->hasPermissionTo('galeriler.view', 'admin'))->toBeTrue();
    expect($photo->fresh()->category->id)->toBe($child->id);
    expect($photo->fresh()->imageMedia->id)->toBe($media->id);
    Storage::disk('public')->assertExists($path);
});

test('galeriler uses a separate identity while the shared media library and picker keep their routes', function () {
    $this->withoutVite();
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.media.index'))->assertOk();
    $this->getJson(route('admin.media.data'))->assertOk();
    $this->get(route('admin.galeriler.create'))->assertOk()
        ->assertSee('data-media-multiple="true"', false)
        ->assertSee('data-media-field', false);
    expect(app(MediaService::class)->configuration($admin)['endpoints']['browse'])->toBe(route('admin.media.data'));
    $this->get('/admin/gallery')->assertNotFound();
    expect(route('admin.galeriler.index', absolute: false))->toBe('/admin/galeriler');
    expect(app('modules')->find('Galeriler')->get('alias'))->toBe('galeriler');
    expect(DB::table('media_files')->count())->toBe(0);
});
