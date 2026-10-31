<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Database\Seeders\AdminPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Branches\Database\Seeders\BranchCategorySeeder;
use Modules\Branches\Models\Branch;
use Modules\Branches\Models\BranchCategory;
use Modules\Branches\Services\BranchesFrontendService;
use Modules\Konum\Models\City;
use Modules\Konum\Models\Town;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $disk = Storage::fake('branches-test-statuses');
    $disk->put('statuses.json', json_encode(['Branches' => true, 'Konum' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    $this->languageId = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true])->id;
});

test('the install seeder creates the branch and dealer categories once', function () {
    $this->seed(BranchCategorySeeder::class);
    $this->seed(BranchCategorySeeder::class);

    $this->assertDatabaseCount('branch_categories', 2);
    $this->assertDatabaseHas('branch_categories', ['slug' => 'branch', 'name' => 'Şubeler']);
    $this->assertDatabaseHas('branch_categories', ['slug' => 'dealer', 'name' => 'Bayiler']);
});

test('an admin can create a branch without optional location values', function () {
    $this->seed(BranchCategorySeeder::class);
    $category = BranchCategory::query()->where('slug', 'branch')->firstOrFail();
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.branches.store'), [
        'lang_id' => $this->languageId,
        'branch_category_id' => $category->id,
        'name' => 'Kadıköy Ofisi',
        'is_active' => '1',
        'working_hours' => 'Hafta içi 09.00–18.00',
    ])->assertRedirect(route('admin.branches.edit', ['branch' => 1, 'lang' => 'tr']))->assertSessionHas('notification.message', 'Şube / bayi başarıyla eklendi.');

    $this->assertDatabaseHas('branches', [
        'name' => 'Kadıköy Ofisi', 'branch_category_id' => $category->id, 'city_id' => null, 'town_id' => null,
        'working_hours' => 'Hafta içi 09.00–18.00', 'is_active' => true,
    ]);
    $this->assertDatabaseCount('notifications', 0);
});

test('the branch form renders optional location fields and the table top bar category filter', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $this->seed(BranchCategorySeeder::class);

    $this->actingAs($admin, 'admin')->get(route('admin.branches.index'))->assertOk()
        ->assertSee('data-table-filter="category_id"', false)->assertSee('Şube')->assertSee('Bayi');
    $this->get(route('admin.branches.create'))->assertOk()
        ->assertSee('Şube / bayi adı')->assertSee('name="city_id"', false)
        ->assertSee('class="form-select js-select2"', false)->assertSee('data-placeholder="İl seçiniz"', false)
        ->assertDontSee('city_id-search', false)->assertSee('name="working_hours"', false)->assertSee('Hafta içi 09.00–18.00')
        ->assertSee('Türkiye Konum verisi hazır değil');
});

test('an admin can update and delete a branch', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $category = BranchCategory::factory()->create(['slug' => 'branch', 'name' => 'Şubeler']);
    $branch = Branch::factory()->for($category, 'category')->create(['name' => 'Eski ad']);
    $this->actingAs($admin, 'admin');

    $this->put(route('admin.branches.update', $branch), [
        'lang_id' => $this->languageId,
        'branch_category_id' => $category->id,
        'name' => 'Yeni ad',
        'is_active' => '1',
    ])->assertRedirect(route('admin.branches.edit', ['branch' => $branch->id, 'lang' => 'tr']));
    $this->assertDatabaseHas('branches', ['id' => $branch->id, 'name' => 'Yeni ad']);

    $this->delete(route('admin.branches.destroy', $branch))->assertRedirectToRoute('admin.branches.index');
    $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
});

test('branch translations update independently by active language', function () {
    $this->seed(BranchCategorySeeder::class);
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $category = BranchCategory::query()->where('slug', 'branch')->firstOrFail();
    $branch = Branch::factory()->for($category, 'category')->create(['name' => 'İstanbul Ofisi', 'phone' => '02120000000']);
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->put(route('admin.branches.update', ['branch' => $branch->id, 'lang' => 'en']), [
        'lang_id' => $english->id,
        'branch_category_id' => $category->id,
        'name' => 'Istanbul Office',
        'phone' => '+90 212 000 00 00',
        'is_active' => '1',
    ])->assertRedirect(route('admin.branches.edit', ['branch' => $branch->id, 'lang' => 'en']));

    $this->assertDatabaseHas('branch_translations', ['branch_id' => $branch->id, 'lang_id' => $this->languageId, 'name' => 'İstanbul Ofisi', 'phone' => '02120000000']);
    $this->assertDatabaseHas('branch_translations', ['branch_id' => $branch->id, 'lang_id' => $english->id, 'name' => 'Istanbul Office', 'phone' => '+90 212 000 00 00']);
    $this->assertDatabaseHas('branches', ['id' => $branch->id, 'name' => 'İstanbul Ofisi']);
});

test('branch edit form prefills fields from the selected translation', function () {
    $category = BranchCategory::factory()->create(['slug' => 'branch', 'name' => 'Şubeler']);
    $branch = Branch::factory()->for($category, 'category')->create(['name' => 'İstanbul Ofisi', 'phone' => '02120000000', 'email' => 'istanbul@example.test']);
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.branches.edit', ['branch' => $branch->id, 'lang' => 'tr']))
        ->assertOk()
        ->assertSee('value="İstanbul Ofisi"', false)
        ->assertSee('value="02120000000"', false)
        ->assertSee('value="istanbul@example.test"', false);
});

test('branch creation rejects an invalid location and partial coordinates', function () {
    $this->seed(BranchCategorySeeder::class);
    $admin = Admin::factory()->superAdmin()->create();
    $category = BranchCategory::query()->firstOrFail();

    $this->actingAs($admin, 'admin')->post(route('admin.branches.store'), [
        'lang_id' => $this->languageId,
        'branch_category_id' => $category->id,
        'name' => 'Geçersiz adres',
        'city_id' => 99999999,
        'latitude' => '41.0',
        'is_active' => '1',
    ])->assertSessionHasErrors(['city_id', 'longitude']);

    $this->assertDatabaseCount('branches', 0);
});

test('branch creation validates a selected location against the Konum service', function () {
    $this->seed(BranchCategorySeeder::class);
    config(['konum.source.commit' => 'test-konum-source']);
    DB::table('konum_imports')->insert([
        'source_commit' => 'test-konum-source', 'status' => 'complete', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $city = City::factory()->create(['is_consistent' => true]);
    $category = BranchCategory::query()->firstOrFail();
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.branches.store'), [
        'lang_id' => $this->languageId,
        'branch_category_id' => $category->id,
        'name' => 'Ankara Şubesi',
        'city_id' => $city->id,
        'is_active' => '1',
    ])->assertRedirect(route('admin.branches.edit', ['branch' => 1, 'lang' => 'tr']));

    $this->assertDatabaseHas('branches', ['name' => 'Ankara Şubesi', 'city_id' => $city->id]);
});

test('an admin can retrieve searchable dependent location options', function () {
    config(['konum.source.commit' => 'test-konum-source']);
    DB::table('konum_imports')->insert([
        'source_commit' => 'test-konum-source', 'status' => 'complete', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $city = City::factory()->create(['name' => 'Ankara', 'is_consistent' => true]);
    $town = Town::factory()->create(['city_id' => $city->id, 'name' => 'Çankaya', 'is_consistent' => true]);
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->getJson(route('admin.branches.location-options', [
        'entity' => 'cities',
        'search' => 'anka',
    ]))->assertOk()->assertJsonPath('items.0.id', $city->id)->assertJsonPath('items.0.name', 'Ankara');

    $this->getJson(route('admin.branches.location-options', [
        'entity' => 'towns',
        'city_id' => $city->id,
    ]))->assertOk()->assertJsonPath('items.0.id', $town->id)->assertJsonPath('items.0.name', 'Çankaya');
});

test('branch creation rejects a child location that belongs to another city', function () {
    $this->seed(BranchCategorySeeder::class);
    config(['konum.source.commit' => 'test-konum-source']);
    DB::table('konum_imports')->insert([
        'source_commit' => 'test-konum-source', 'status' => 'complete', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $actualCity = City::factory()->create(['is_consistent' => true]);
    $otherCity = City::factory()->create(['is_consistent' => true]);
    $town = Town::factory()->create(['city_id' => $actualCity->id, 'is_consistent' => true]);
    $category = BranchCategory::query()->firstOrFail();
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.branches.store'), [
        'lang_id' => $this->languageId,
        'branch_category_id' => $category->id,
        'name' => 'Uyumsuz konum',
        'city_id' => $otherCity->id,
        'town_id' => $town->id,
        'is_active' => '1',
    ])->assertSessionHasErrors('city_id');

    $this->assertDatabaseCount('branches', 0);
});

test('branch listing filters by category through the table top bar request', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $branchCategory = BranchCategory::factory()->create(['slug' => 'branch', 'name' => 'Şubeler']);
    $dealerCategory = BranchCategory::factory()->create(['slug' => 'dealer', 'name' => 'Bayiler']);
    $branch = Branch::factory()->for($branchCategory, 'category')->create(['name' => 'Merkez']);
    Branch::factory()->for($dealerCategory, 'category')->create(['name' => 'Bayi Noktası']);

    $this->actingAs($admin, 'admin')->getJson(route('admin.branches.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'category_id' => $branchCategory->id,
        'columns' => [['data' => 'name', 'name' => 'localized.name', 'searchable' => 'true', 'orderable' => 'true']],
    ]))->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', $branch->id);
});

test('frontend reads active branches from active categories in configured order', function () {
    config(['konum.source.commit' => 'test-konum-source']);
    DB::table('konum_imports')->insert([
        'source_commit' => 'test-konum-source', 'status' => 'complete', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $visibleCategory = BranchCategory::factory()->create(['slug' => 'branch', 'is_active' => true]);
    $hiddenCategory = BranchCategory::factory()->create(['slug' => 'old', 'is_active' => false]);
    $later = Branch::factory()->for($visibleCategory, 'category')->create(['sort_order' => 10, 'is_active' => true]);
    $first = Branch::factory()->for($visibleCategory, 'category')->create(['sort_order' => 1, 'is_active' => true]);
    Branch::factory()->for($visibleCategory, 'category')->create(['is_active' => false]);
    Branch::factory()->for($hiddenCategory, 'category')->create(['is_active' => true]);

    $service = app(BranchesFrontendService::class);

    expect($service->getAll('branch')->modelKeys())->toBe([$first->id, $later->id]);
    expect($service->find($first->id)?->id)->toBe($first->id);
    expect($service->find($later->id + 1000))->toBeNull();
});

test('frontend returns the selected locale translation and hides untranslated records', function () {
    config(['konum.source.commit' => 'test-konum-source']);
    DB::table('konum_imports')->insert([
        'source_commit' => 'test-konum-source', 'status' => 'complete', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $category = BranchCategory::factory()->create(['slug' => 'branch', 'is_active' => true]);
    $category->translations()->create(['lang_id' => $english->id, 'name' => 'Branches']);
    $translated = Branch::factory()->for($category, 'category')->create(['name' => 'İstanbul Ofisi', 'phone' => '02120000000']);
    $translated->translations()->create(['lang_id' => $english->id, 'name' => 'Istanbul Office', 'phone' => '+90 212 000 00 00']);
    Branch::factory()->for($category, 'category')->create(['name' => 'Çevirisiz Ofis']);
    app()->setLocale('en');

    $branches = app(BranchesFrontendService::class)->getAll('branch');

    expect($branches->modelKeys())->toBe([$translated->id])
        ->and($branches->first()->name)->toBe('Istanbul Office')
        ->and($branches->first()->phone)->toBe('+90 212 000 00 00')
        ->and($branches->first()->category->name)->toBe('Branches');
});

test('frontend returns no branches when Konum is disabled', function () {
    $category = BranchCategory::factory()->create(['is_active' => true]);
    Branch::factory()->for($category, 'category')->create(['is_active' => true]);
    Storage::disk('branches-test-statuses')->put('statuses.json', json_encode(['Branches' => true, 'Konum' => false], JSON_THROW_ON_ERROR));
    app()->forgetInstance(ActivatorInterface::class);

    expect(app(BranchesFrontendService::class)->getAll())->toBeEmpty();
});

test('the branch menu requires both menu and view permissions', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk()->assertDontSee('Şubeler / Bayiler');

    $admin->givePermissionTo('branches.menu');
    $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('Şubeler / Bayiler');

    $admin->givePermissionTo('branches.view');
    $this->get(route('admin.dashboard'))->assertOk()->assertSee('Şubeler / Bayiler');
});

test('branch write routes enforce operation permissions', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $category = BranchCategory::factory()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.branches.store'), [
        'lang_id' => $this->languageId,
        'branch_category_id' => $category->id,
        'name' => 'Yetkisiz kayıt',
        'is_active' => '1',
    ])->assertForbidden();

    $this->assertDatabaseCount('branches', 0);
});
