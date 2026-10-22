<?php

use App\Models\Admin\Admin;
use Database\Seeders\AdminPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Branches\Database\Seeders\BranchCategorySeeder;
use Modules\Branches\Models\Branch;
use Modules\Branches\Models\BranchCategory;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $disk = Storage::fake('branches-category-statuses');
    $disk->put('statuses.json', json_encode(['Branches' => true, 'Konum' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('category management renders its pages and lists the linked record count', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $category = BranchCategory::factory()->create(['name' => 'Temsilcilikler']);
    Branch::factory()->for($category, 'category')->create();
    $this->actingAs($admin, 'admin')->get(route('admin.branches.categories.index'))->assertOk()->assertSee('Kategori ekle');
    $this->get(route('admin.branches.categories.create'))->assertOk()->assertSee('name="slug"', false);
    $this->get(route('admin.branches.categories.edit', $category))->assertOk()->assertSee('Temsilcilikler');
    $this->getJson(route('admin.branches.categories.data', ['draw' => 1, 'start' => 0, 'length' => 10]))
        ->assertOk()->assertJsonPath('data.0.branches_count', 1)->assertJsonPath('data.0.name', 'Temsilcilikler');
});

test('an admin creates updates and deletes a category with notification links', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['branches.categories.create', 'branches.categories.update', 'branches.categories.delete']);
    $this->actingAs($admin, 'admin')->post(route('admin.branches.categories.store'), [
        'name' => 'Temsilcilikler', 'slug' => '', 'sort_order' => 3, 'is_active' => 1,
    ])->assertRedirectToRoute('admin.branches.categories.index');
    $category = BranchCategory::query()->where('slug', 'temsilcilikler')->firstOrFail();
    $this->assertDatabaseHas('branch_categories', ['id' => $category->id, 'name' => 'Temsilcilikler', 'sort_order' => 3]);
    expect($admin->notifications()->first()->data['link'])->toBe(route('admin.branches.categories.edit', $category, absolute: false));
    $this->put(route('admin.branches.categories.update', $category), [
        'name' => 'Ofisler', 'slug' => 'temsilcilikler', 'sort_order' => 4, 'is_active' => 0,
    ])->assertRedirectToRoute('admin.branches.categories.index');
    $this->assertDatabaseHas('branch_categories', ['id' => $category->id, 'name' => 'Ofisler', 'is_active' => false]);
    expect($admin->notifications()->where('data->title', 'Şube / bayi kategorisi güncellendi')->first()->data['link'])
        ->toBe(route('admin.branches.categories.edit', $category, absolute: false));
    $this->delete(route('admin.branches.categories.destroy', $category))->assertRedirectToRoute('admin.branches.categories.index');
    $this->assertDatabaseMissing('branch_categories', ['id' => $category->id]);
    expect($admin->notifications()->where('data->title', 'Şube / bayi kategorisi silindi')->first()->data['link'])
        ->toBe(route('admin.branches.categories.index', absolute: false));
});

test('a super admin category operation does not create notifications', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.branches.categories.store'), [
        'name' => 'Ofisler', 'slug' => 'ofisler', 'sort_order' => 0, 'is_active' => 1,
    ])->assertRedirectToRoute('admin.branches.categories.index');
    $this->assertDatabaseHas('branch_categories', ['slug' => 'ofisler']);
    $this->assertDatabaseCount('notifications', 0);
});

test('a category with linked branches cannot be deleted', function () {
    $category = BranchCategory::factory()->create();
    $branch = Branch::factory()->for($category, 'category')->create();
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->from(route('admin.branches.categories.index'))
        ->delete(route('admin.branches.categories.destroy', $category))
        ->assertRedirectToRoute('admin.branches.categories.index')
        ->assertSessionHasErrors(['category' => 'Bu kategoriye bağlı kayıtlar var. Silmeden önce kayıtları başka kategoriye taşıyın.']);
    $this->assertDatabaseHas('branch_categories', ['id' => $category->id]);
    $this->assertDatabaseHas('branches', ['id' => $branch->id]);
});

test('duplicate category slugs and invalid category input are rejected', function () {
    BranchCategory::factory()->create(['slug' => 'ofisler']);
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.branches.categories.store'), [
        'name' => '', 'slug' => 'ofisler', 'sort_order' => -1, 'is_active' => 'invalid',
    ])->assertSessionHasErrors(['name', 'slug', 'sort_order', 'is_active']);
    $this->assertDatabaseCount('branch_categories', 1);
});

test('category routes require their own permissions and the menu follows category permissions', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $category = BranchCategory::factory()->create();
    $this->actingAs($admin, 'admin')->get(route('admin.branches.categories.index'))->assertForbidden();
    $this->post(route('admin.branches.categories.store'), [])->assertForbidden();
    $this->put(route('admin.branches.categories.update', $category), [])->assertForbidden();
    $this->delete(route('admin.branches.categories.destroy', $category))->assertForbidden();
    $admin->givePermissionTo('branches.categories.menu');
    $this->get(route('admin.dashboard'))->assertDontSee(route('admin.branches.categories.index'));
    $admin->givePermissionTo('branches.categories.view');
    $this->get(route('admin.dashboard'))->assertOk()->assertSee(route('admin.branches.categories.index'));
});

test('the seeder preserves category edits and custom categories', function () {
    $category = BranchCategory::factory()->create(['slug' => 'branch', 'name' => 'Merkezler', 'sort_order' => 5, 'is_active' => false]);
    BranchCategory::factory()->create(['slug' => 'ofisler']);
    $this->seed(BranchCategorySeeder::class);
    $this->seed(BranchCategorySeeder::class);
    $this->assertDatabaseCount('branch_categories', 3);
    $this->assertDatabaseHas('branch_categories', ['id' => $category->id, 'name' => 'Merkezler', 'sort_order' => 5, 'is_active' => false]);
    $this->assertDatabaseHas('branch_categories', ['slug' => 'dealer', 'name' => 'Bayiler']);
});

test('inactive categories cannot be newly selected but existing branch categories can be retained', function () {
    $inactive = BranchCategory::factory()->create(['is_active' => false, 'name' => 'Eski Kategori']);
    $otherInactive = BranchCategory::factory()->create(['is_active' => false, 'name' => 'Başka Pasif']);
    $branch = Branch::factory()->for($inactive, 'category')->create();
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->get(route('admin.branches.create'))->assertOk()->assertDontSee('Eski Kategori');
    $this->get(route('admin.branches.edit', $branch))->assertOk()->assertSee('Eski Kategori')->assertDontSee('Başka Pasif');
    $data = ['name' => 'Merkez', 'branch_category_id' => $inactive->id, 'is_active' => 1];
    $this->post(route('admin.branches.store'), $data)->assertSessionHasErrors('branch_category_id');
    $this->put(route('admin.branches.update', $branch), $data)->assertSessionDoesntHaveErrors()->assertRedirectToRoute('admin.branches.index');
    $this->put(route('admin.branches.update', $branch), array_replace($data, ['branch_category_id' => $otherInactive->id]))
        ->assertSessionHasErrors('branch_category_id');
    $this->assertDatabaseHas('branches', ['id' => $branch->id, 'branch_category_id' => $inactive->id, 'name' => 'Merkez']);
});

test('category management requires authentication and an enabled module', function () {
    $this->get(route('admin.branches.categories.index'))->assertRedirect();
    $admin = Admin::factory()->superAdmin()->create();
    Storage::disk('branches-category-statuses')->put('statuses.json', json_encode(['Branches' => false, 'Konum' => true], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);
    $this->actingAs($admin, 'admin')->get(route('admin.branches.categories.index'))->assertNotFound();
});

test('the default category name upgrade preserves custom names and branch relationships', function () {
    $default = BranchCategory::factory()->create(['slug' => 'branch', 'name' => 'Şube']);
    $custom = BranchCategory::factory()->create(['slug' => 'dealer', 'name' => 'Yetkili Satıcılar']);
    $branch = Branch::factory()->for($default, 'category')->create();
    $migration = require base_path('Modules/Branches/database/migrations/2026_10_22_070335_update_default_branch_category_names.php');
    $migration->up();

    $this->assertDatabaseHas('branch_categories', ['id' => $default->id, 'slug' => 'branch', 'name' => 'Şubeler']);
    $this->assertDatabaseHas('branch_categories', ['id' => $custom->id, 'name' => 'Yetkili Satıcılar']);
    $this->assertDatabaseHas('branches', ['id' => $branch->id, 'branch_category_id' => $default->id]);
});
