<?php

use App\Models\Admin\Admin;
use App\Models\Forms\FormField;
use App\Models\Forms\FormSubmission;
use App\Models\Forms\SiteForm;
use App\Models\Media\MediaFile;
use App\Services\Forms\FormContext;
use Database\Seeders\AdminPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\ProductsFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function productImage(string $name): MediaFile
{
    $upload = UploadedFile::fake()->image($name);
    $path = $upload->store('_media', 'public');

    return MediaFile::factory()->create(['path' => $path, 'name' => $name, 'kind' => 'image', 'mime_type' => 'image/png', 'extension' => 'png', 'size' => Storage::disk('public')->size($path)]);
}

function productForm(): SiteForm
{
    return SiteForm::factory()->has(FormField::factory(), 'fields')->create(['is_active' => true]);
}

function disableProducts(): void
{
    $disk = Storage::fake('products-status');
    $disk->put('statuses.json', json_encode(['Products' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
}

test('products default to published with generated editable slug and optional fields absent', function () {
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.products.store'), ['title' => 'Çelik Kapı', 'id' => 999])->assertRedirectToRoute('admin.products.index');

    $this->assertDatabaseHas('products', ['title' => 'Çelik Kapı', 'slug' => 'celik-kapi', 'sort_order' => 0, 'is_active' => true, 'site_form_id' => null, 'product_category_id' => null]);
    $this->assertDatabaseCount('notifications', 0);
    expect(Product::query()->sole()->id)->not->toBe(999);
    $this->get(route('admin.products.edit', Product::query()->sole()))->assertOk()->assertSee('Form gösterme')->assertSee('Taslak');
});

test('product and category forms and tables follow the common admin flow', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $category = ProductCategory::factory()->create(['name' => 'Kapılar']);
    $form = productForm();
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.products.index'))->assertOk()->assertSee('data-table-filter="category_id"', false)->assertSee('data-table-filter="is_active"', false)->assertSee('Ürün ekle');
    $this->get(route('admin.products.create'))->assertOk()->assertSee('name="title"', false)->assertSee('name="image_media_ids"', false)->assertSee($form->name)->assertSee($category->name);
    $this->get(route('admin.products.categories.index'))->assertOk();
    $this->get(route('admin.products.categories.create'))->assertOk();
});

test('products save reordered gallery cover category and optional shared form', function () {
    Storage::fake('public');
    $admin = Admin::factory()->superAdmin()->create();
    $category = ProductCategory::factory()->create();
    $form = productForm();
    $first = productImage('one.png');
    $second = productImage('two.png');
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.products.store'), ['title' => 'Kapı', 'slug' => 'ozel-kapi', 'product_category_id' => $category->id, 'site_form_id' => $form->id, 'image_media_id' => $first->id, 'image_media_ids' => json_encode([$first->id, $second->id]), 'content' => '<p>Detay</p><script>alert(1)</script>'])->assertRedirectToRoute('admin.products.index');
    $product = Product::query()->sole();
    expect($product->images->modelKeys())->toBe([$first->id, $second->id]);
    expect($product->content)->not->toContain('<script>');
    $this->assertDatabaseHas('media_usages', ['owner_id' => $product->id, 'owner_type' => Product::class, 'field' => 'product_image.'.$second->id]);

    $this->put(route('admin.products.update', $product), ['title' => 'Yeni kapı', 'slug' => 'duzenlenen-kapi', 'image_media_ids' => [$second->id, $first->id], 'is_active' => 0, 'sort_order' => 8, 'site_form_id' => $form->id])->assertRedirectToRoute('admin.products.index');
    expect($product->fresh()->images->modelKeys())->toBe([$second->id, $first->id]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'slug' => 'duzenlenen-kapi', 'sort_order' => 8, 'is_active' => false]);
    $this->delete(route('admin.products.destroy', $product))->assertRedirectToRoute('admin.products.index');
    $this->assertDatabaseCount('product_images', 0);
    $this->assertDatabaseCount('media_usages', 0);
    $this->assertDatabaseCount('media_files', 2);
});

test('product rejects invalid payload without writing records', function (array $payload, string $field) {
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.products.store'), ['title' => 'Kapı', ...$payload])->assertSessionHasErrors($field);

    $this->assertDatabaseCount('products', 0);
    $this->assertDatabaseCount('notifications', 0);
})->with([
    'blank name' => [['title' => '  '], 'title'],
    'invalid category' => [['product_category_id' => 999999], 'product_category_id'],
    'invalid form' => [['site_form_id' => 999999], 'site_form_id'],
    'invalid publication' => [['is_active' => 'unknown'], 'is_active'],
    'negative ordering' => [['sort_order' => -1], 'sort_order'],
    'invalid slug' => [['slug' => '../unsafe'], 'slug'],
    'invalid media' => [['image_media_ids' => [999999]], 'image_media_ids.0'],
    'malformed gallery' => [['image_media_ids' => 'invalid'], 'image_media_ids'],
]);

test('duplicate slugs are rejected while existing product can keep its own slug', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $product = Product::factory()->create(['slug' => 'kapi']);
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.products.store'), ['title' => 'Kapı', 'slug' => 'kapi'])->assertSessionHasErrors('slug');
    $this->put(route('admin.products.update', $product), ['title' => 'Kapı', 'slug' => 'kapi'])->assertRedirectToRoute('admin.products.index');

    $this->assertDatabaseCount('products', 1);
});

test('category CRUD preserves products on category removal and sends actor notifications', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['products.categories.create', 'products.categories.update', 'products.categories.delete']);
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.products.categories.store'), ['name' => 'Kapılar'])->assertRedirectToRoute('admin.products.categories.index');
    $category = ProductCategory::query()->sole();
    $product = Product::factory()->for($category, 'category')->create();
    expect($admin->notifications()->latest()->first()->data['link'])->toBe(route('admin.products.categories.edit', $category, absolute: false));
    $this->put(route('admin.products.categories.update', $category), ['name' => 'İç kapılar', 'is_active' => 0, 'sort_order' => 3])->assertRedirectToRoute('admin.products.categories.index');
    $this->assertDatabaseHas('product_categories', ['id' => $category->id, 'name' => 'İç kapılar', 'is_active' => false, 'sort_order' => 3]);
    $this->delete(route('admin.products.categories.destroy', $category))->assertRedirectToRoute('admin.products.categories.index');
    expect($product->fresh()->product_category_id)->toBeNull();
    expect($admin->notifications()->count())->toBe(3);
});

test('product CRUD notifies every admin with edit and list destinations', function () {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $other = Admin::factory()->superAdmin()->create();
    $admin->givePermissionTo(['products.create', 'products.update', 'products.delete']);
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.products.store'), ['title' => 'Kapı'])->assertRedirectToRoute('admin.products.index');
    $product = Product::query()->sole();
    expect($admin->notifications()->first()->data['link'])->toBe(route('admin.products.edit', $product, absolute: false));
    $this->put(route('admin.products.update', $product), ['title' => 'İç kapı'])->assertRedirectToRoute('admin.products.index');
    $this->delete(route('admin.products.destroy', $product))->assertRedirectToRoute('admin.products.index');

    expect($other->notifications()->count())->toBe(3);
    expect($admin->notifications()->where('data->title', 'Ürün silindi')->sole()->data['link'])->toBe(route('admin.products.index', absolute: false));
});

test('guest inactive account and unprivileged admin cannot mutate products', function () {
    $this->get(route('admin.products.create'))->assertRedirectToRoute('admin.login');
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.products.store'), ['title' => 'Denied'])->assertForbidden();
    $inactive = Admin::factory()->superAdmin()->inactive()->create();
    $this->actingAs($inactive, 'admin')->post(route('admin.products.store'), ['title' => 'Denied'])->assertRedirectToRoute('admin.login');

    $this->assertDatabaseCount('products', 0);
});

test('category and publication filters combine with table search', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $category = ProductCategory::factory()->create();
    $wanted = Product::factory()->for($category, 'category')->create(['title' => '<b>Kapı</b>', 'is_active' => false]);
    Product::factory()->for($category, 'category')->create(['title' => 'Kapı', 'is_active' => true]);
    Product::factory()->create(['title' => 'Kapı', 'is_active' => false]);

    $this->actingAs($admin, 'admin')->getJson(route('admin.products.data', ['category_id' => $category->id, 'is_active' => '0', 'search' => ['value' => 'Kapı'], 'columns' => [['data' => 'title', 'name' => 'products.title', 'searchable' => 'true', 'orderable' => 'true']]]))->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', $wanted->id)->assertJsonPath('data.0.title', e($wanted->title));
});

test('public reads exclude drafts and hidden categories and use deterministic ordering', function () {
    $hidden = ProductCategory::factory()->create(['is_active' => false]);
    $category = ProductCategory::factory()->create(['is_active' => true]);
    Product::factory()->for($hidden, 'category')->create();
    $draft = Product::factory()->create(['is_active' => false]);
    $later = Product::factory()->create(['sort_order' => 5]);
    $first = Product::factory()->for($category, 'category')->create(['sort_order' => 0]);
    $service = app(ProductsFrontendService::class);

    expect($service->products()->modelKeys())->toBe([$first->id, $later->id]);
    expect($service->products($category->id)->modelKeys())->toBe([$first->id]);
    expect($service->products(limit: 1)->modelKeys())->toBe([$first->id]);
    expect($service->categories()->modelKeys())->toBe([$category->id]);
    expect($service->findBySlug($draft->slug))->toBeNull();
    expect($service->findBySlug($first->slug)?->id)->toBe($first->id);
    expect($service->products(999999))->toBeEmpty();
    expect($service->products(limit: 0))->toBeEmpty();
});

test('disabled module blocks admin public reads and product form contexts', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $product = Product::factory()->create();
    disableProducts();
    $service = app(ProductsFrontendService::class);

    $this->actingAs($admin, 'admin')->get(route('admin.products.index'))->assertNotFound();
    $this->post(route('admin.products.store'), ['title' => 'Denied'])->assertNotFound();
    expect($service->products())->toBeEmpty();
    expect($service->categories())->toBeEmpty();
    expect($service->findBySlug($product->slug))->toBeNull();
    expect($service->formFor($product))->toBeNull();
});

test('one existing form accepts different products and retains snapshots after removal', function () {
    $form = productForm();
    $first = Product::factory()->create(['site_form_id' => $form->id, 'title' => '<script>Kapı</script>']);
    $second = Product::factory()->create(['site_form_id' => $form->id]);
    $service = app(ProductsFrontendService::class);
    foreach ([$first, $second] as $product) {
        $context = $service->formFor($product);
        $this->postJson(route('forms.submit', $form->key), ['form_context' => $context['context'], 'answers' => ['message' => 'Bilgi istiyorum']])->assertCreated();
    }
    $submission = FormSubmission::query()->oldest('id')->firstOrFail();
    $first->delete();
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.forms.submissions.show', $submission))->assertOk()->assertSee('İlgili ürün:')->assertSee('<script>Kapı</script>')->assertDontSee('<script>Kapı</script>', false);
    expect($submission->related_content['id'])->toBe($first->id);
    expect(FormSubmission::query()->latest('id')->first()->related_content['id'])->toBe($second->id);
});

test('form context is explicitly rendered and absent for ordinary forms', function () {
    $form = productForm();
    $product = Product::factory()->create(['site_form_id' => $form->id]);
    $data = app(ProductsFrontendService::class)->formFor($product);

    view()->share('errors', new ViewErrorBag);
    $html = Blade::render('<x-site-form :key="$key" :context="$context" unstyled />', $data);
    expect($html)->toContain('name="form_context"');
    expect(Blade::render('<x-site-form :key="$key" unstyled />', ['key' => $form->key]))->not->toContain('name="form_context"');
    $this->postJson(route('forms.submit', $form->key), ['answers' => ['message' => 'İletişim'], 'product_id' => $product->id])->assertCreated();
    expect(FormSubmission::query()->sole()->related_content)->toBeNull();
});

test('submission rejects forged expired mismatched or no longer public product contexts', function (string $failure) {
    $this->freezeTime();
    $form = productForm();
    $category = ProductCategory::factory()->create(['is_active' => true]);
    $product = Product::factory()->for($category, 'category')->create(['site_form_id' => $form->id]);
    $data = app(ProductsFrontendService::class)->formFor($product);
    $token = $data['context'];
    match ($failure) {
        'forged' => $token = 'forged-token',
        'expired' => $this->travel(3)->hours(),
        'draft' => $product->update(['is_active' => false]),
        'deleted' => $product->delete(),
        'changed form' => $product->update(['site_form_id' => null]),
        'hidden category' => $category->update(['is_active' => false]),
        'disabled module' => disableProducts(),
        'wrong form' => $form = productForm(),
    };

    $this->postJson(route('forms.submit', $form->key), ['form_context' => $token, 'answers' => ['message' => 'Mesaj']])->assertUnprocessable()->assertJsonValidationErrors('form_context');

    $this->assertDatabaseCount('form_submissions', 0);
})->with(['forged', 'expired', 'draft', 'deleted', 'changed form', 'hidden category', 'disabled module', 'wrong form']);

test('no form is exposed when absent inactive or empty', function () {
    $product = Product::factory()->create();
    $service = app(ProductsFrontendService::class);
    expect($service->formFor($product))->toBeNull();
    $form = SiteForm::factory()->create(['is_active' => true]);
    $product->update(['site_form_id' => $form->id]);
    expect($service->formFor($product))->toBeNull();
    FormField::factory()->for($form, 'form')->create();
    $form->update(['is_active' => false]);
    expect($service->formFor($product))->toBeNull();
});

test('every protected mutation checks its own product or category permission', function (string $action, string $method, bool $categoryRoute) {
    $admin = Admin::factory()->create();
    $record = $categoryRoute ? ProductCategory::factory()->create() : Product::factory()->create();

    $this->actingAs($admin, 'admin')->{$method}(route('admin.products.'.$action, $record), ['title' => 'Denied', 'name' => 'Denied'])->assertForbidden();

    $this->assertModelExists($record);
})->with([
    'update product' => ['update', 'put', false],
    'delete product' => ['destroy', 'delete', false],
    'update category' => ['categories.update', 'put', true],
    'delete category' => ['categories.destroy', 'delete', true],
]);

test('module feature definitions render validate and persist through shared fields', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $disk = Storage::fake('product-features');
    $disk->put('Products/module.json', json_encode(['name' => 'Products', 'providers' => []], JSON_THROW_ON_ERROR));
    $disk->put('Products/features.json', json_encode([
        'schema_version' => 1,
        'forms' => ['product' => ['fields' => [
            ['key' => 'caption', 'label' => 'Ürün etiketi', 'input_type' => 'text', 'value_type' => 'string', 'validation' => ['required' => true, 'max' => 50]],
        ]]],
    ], JSON_THROW_ON_ERROR));
    $disk->put('statuses.json', json_encode(['Products' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.products.create'))->assertOk()->assertSee('name="extra_fields[caption]"', false);
    $this->post(route('admin.products.store'), ['title' => 'Eksik'])->assertSessionHasErrors(['extra_fields.caption' => 'Ürün etiketi alanını doldurun.']);
    $this->assertDatabaseCount('products', 0);
    $this->post(route('admin.products.store'), ['title' => 'Tam', 'extra_fields' => ['caption' => 'Dayanıklı']])->assertRedirectToRoute('admin.products.index');
    expect(Product::query()->sole()->extra_fields)->toBe(['caption' => 'Dayanıklı']);
});

test('gallery selection rejects duplicate images and new selection without media permission', function () {
    Storage::fake('public');
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo('products.create');
    $image = productImage('image.png');
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.products.store'), ['title' => 'Kapı', 'image_media_ids' => [$image->id, $image->id]])->assertSessionHasErrors('image_media_ids.0');
    $this->post(route('admin.products.store'), ['title' => 'Kapı', 'image_media_ids' => [$image->id]])->assertForbidden();
    $this->assertDatabaseCount('products', 0);
    $this->assertDatabaseCount('media_usages', 0);
});

test('ordinary forms keep working without any optional context resolver', function () {
    $form = productForm();
    app()->instance(FormContext::class, new FormContext);

    $this->postJson(route('forms.submit', $form->key), ['answers' => ['message' => 'Genel başvuru']])->assertCreated();

    expect(FormSubmission::query()->sole()->related_content)->toBeNull();
});

test('inactive forms reject a previously issued product context', function () {
    $form = productForm();
    $product = Product::factory()->create(['site_form_id' => $form->id]);
    $data = app(ProductsFrontendService::class)->formFor($product);
    $form->update(['is_active' => false]);

    $this->postJson(route('forms.submit', $form->key), ['form_context' => $data['context'], 'answers' => ['message' => 'Mesaj']])->assertNotFound();

    $this->assertDatabaseCount('form_submissions', 0);
});

test('product menu requires both menu and view permissions', function (array $permissions, bool $visible) {
    $this->seed(AdminPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->givePermissionTo($permissions);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();
    $link = 'href="'.route('admin.products.index').'"';
    if ($visible) {
        $response->assertSee($link, false);
    } else {
        $response->assertDontSee($link, false);
    }
})->with([
    'no permissions' => [[], false],
    'menu only' => [['products.menu'], false],
    'view only' => [['products.view'], false],
    'both permissions' => [['products.menu', 'products.view'], true],
]);

test('context from an unavailable optional integration is rejected without class resolution errors', function () {
    $form = productForm();
    $product = Product::factory()->create(['site_form_id' => $form->id]);
    $data = app(ProductsFrontendService::class)->formFor($product);
    app()->instance(FormContext::class, new FormContext);

    $this->postJson(route('forms.submit', $form->key), ['form_context' => $data['context'], 'answers' => ['message' => 'Mesaj']])->assertUnprocessable()->assertJsonValidationErrors('form_context');

    $this->assertDatabaseCount('form_submissions', 0);
});
