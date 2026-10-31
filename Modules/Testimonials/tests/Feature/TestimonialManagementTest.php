<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\Language;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Testimonials\Models\Testimonial;

uses(LazilyRefreshDatabase::class);
beforeEach(function () {
    $this->withoutVite();
});

test('testimonials create form selects published while omitted fields use persistence defaults', function () {
    $language = Language::query()->where('code', 'tr')->firstOrFail();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $this->get(route('admin.testimonials.create'))->assertOk()->assertSee('value="1" selected', false)->assertSee('name="sort_order"', false);

    $this->post(route('admin.testimonials.store'), ['lang_id' => $language->id, 'customer_name' => 'Ayşe', 'content' => 'Çok memnun kaldık.'])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.testimonials.index', ['lang' => 'tr']);

    $testimonial = Testimonial::query()->sole();
    $this->assertDatabaseHas('testimonial_translations', ['testimonial_id' => $testimonial->id, 'lang_id' => $language->id, 'customer_name' => 'Ayşe', 'company' => null, 'job_title' => null]);
    $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'is_active' => false, 'sort_order' => 0, 'image_media_id' => null]);
});

test('testimonials require a customer name and comment', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->post(route('admin.testimonials.store'), ['lang_id' => Language::query()->where('code', 'tr')->value('id')])
        ->assertSessionHasErrors(['customer_name' => 'Müşteri adını girin.', 'content' => 'Yorum alanını doldurun.']);
    $this->assertDatabaseCount('testimonials', 0);
});

test('testimonials save shared image publish and order then detach without deleting the image', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->image('customer.png')->store('_media', 'public');
    $image = MediaFile::factory()->create(['path' => $path, 'name' => 'customer.png', 'kind' => 'image', 'extension' => 'png', 'mime_type' => 'image/png', 'size' => Storage::disk('public')->size($path)]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $payload = ['lang_id' => Language::query()->where('code', 'tr')->value('id'), 'customer_name' => 'Müşteri', 'company' => 'Firma', 'job_title' => 'Müdür', 'content' => '<p>Memnunuz.</p><script>bad()</script>', 'is_active' => 1, 'sort_order' => 5, 'image_media_id' => $image->id];

    $this->post(route('admin.testimonials.store'), $payload)->assertSessionHasNoErrors()->assertRedirectToRoute('admin.testimonials.index', ['lang' => 'tr']);

    $testimonial = Testimonial::query()->sole();
    $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'image_media_id' => $image->id, 'sort_order' => 5, 'is_active' => true]);
    $this->assertDatabaseHas('testimonial_translations', ['testimonial_id' => $testimonial->id, 'content' => '<p>Memnunuz.</p>']);
    $this->assertDatabaseHas('media_usages', ['media_id' => $image->id, 'owner_type' => Testimonial::class]);
    $this->get(route('admin.testimonials.edit', $testimonial))->assertOk();
    $payload['sort_order'] = 2;
    $payload['is_active'] = 0;
    $this->put(route('admin.testimonials.update', $testimonial), $payload)->assertSessionHasNoErrors()->assertRedirectToRoute('admin.testimonials.index', ['lang' => 'tr']);
    $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'sort_order' => 2, 'is_active' => false]);
    $this->delete(route('admin.testimonials.destroy', ['testimonial' => $testimonial->id, 'lang' => 'tr']))->assertRedirectToRoute('admin.testimonials.index', ['lang' => 'tr']);
    $this->assertModelMissing($testimonial);
    $this->assertDatabaseMissing('media_usages', ['owner_type' => Testimonial::class, 'owner_id' => $testimonial->id]);
    Storage::disk('public')->assertExists($image->path);
});

test('editing a non-Turkish testimonial translation preserves Turkish text and shared fields', function () {
    $turkish = Language::query()->where('code', 'tr')->firstOrFail();
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $testimonial = Testimonial::factory()->create(['is_active' => true, 'sort_order' => 7]);
    $testimonial->translations()->where('lang_id', $turkish->id)->update(['customer_name' => 'Türkçe ad', 'company' => 'Türk şirket', 'job_title' => 'Unvan', 'content' => 'Türk yorum']);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->put(route('admin.testimonials.update', ['testimonial' => $testimonial->id, 'lang' => 'en']), [
        'lang_id' => $english->id, 'lang' => 'en', 'customer_name' => 'English name', 'company' => 'Company', 'job_title' => 'Title', 'content' => 'English comment',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.testimonials.index', ['lang' => 'en']);

    $this->assertDatabaseHas('testimonial_translations', ['testimonial_id' => $testimonial->id, 'lang_id' => $turkish->id, 'customer_name' => 'Türkçe ad', 'content' => 'Türk yorum']);
    $this->assertDatabaseHas('testimonial_translations', ['testimonial_id' => $testimonial->id, 'lang_id' => $english->id, 'customer_name' => 'English name', 'content' => 'English comment']);
    $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'is_active' => true, 'sort_order' => 7]);
});

test('testimonials public scope excludes unpublished records and respects configured order', function () {
    $later = Testimonial::factory()->create(['is_active' => true, 'sort_order' => 8]);
    Testimonial::factory()->create(['is_active' => false, 'sort_order' => 0]);
    $first = Testimonial::factory()->create(['is_active' => true, 'sort_order' => 1]);

    expect(Testimonial::published()->pluck('id')->all())->toBe([$first->id, $later->id]);
});

test('testimonials reject negative ordering without saving changes', function () {
    $testimonial = Testimonial::factory()->create();
    $languageId = Language::query()->where('code', 'tr')->value('id');
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')->put(route('admin.testimonials.update', $testimonial), ['lang_id' => $languageId, 'customer_name' => 'Changed', 'content' => 'Changed', 'is_active' => 1, 'sort_order' => -1])->assertSessionHasErrors('sort_order');
    $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'is_active' => false]);
});

test('testimonials table escapes input and displays publication and allowed actions', function () {
    app(ModulePanelService::class)->syncPermissions();
    $admin = Admin::factory()->create();
    $admin->givePermissionTo(['testimonials.view', 'testimonials.update']);
    $record = Testimonial::factory()->create(['sort_order' => 4]);
    $record->translations()->where('lang_id', Language::query()->where('code', 'tr')->value('id'))->update(['customer_name' => '<script>Name</script>', 'company' => '<b>Company</b>', 'job_title' => '<b>Title</b>', 'content' => '<p>Comment</p>']);
    $this->actingAs($admin, 'admin')->get(route('admin.testimonials.index'))->assertOk();
    $this->getJson(route('admin.testimonials.data'))->assertOk()
        ->assertJsonPath('data.0.customer_name', '&lt;script&gt;Name&lt;/script&gt;')
        ->assertJsonPath('data.0.company', '&lt;b&gt;Company&lt;/b&gt;')
        ->assertJsonPath('data.0.job_title', '&lt;b&gt;Title&lt;/b&gt;')
        ->assertJsonPath('data.0.content', 'Comment')
        ->assertJsonPath('data.0.sort_order', 4)
        ->assertJsonPath('data.0.is_active', fn (string $status): bool => str_contains($status, 'Yayında değil'))
        ->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, 'Düzenle') && ! str_contains($actions, '>Sil<'));
});

test('testimonials protect reads and writes from unauthorized admins', function () {
    $this->get(route('admin.testimonials.index'))->assertRedirectToRoute('admin.login');
    $testimonial = Testimonial::factory()->create();
    $this->actingAs(Admin::factory()->create(), 'admin')->get(route('admin.testimonials.index'))->assertForbidden();
    $this->post(route('admin.testimonials.store'), [])->assertForbidden();
    $this->put(route('admin.testimonials.update', $testimonial), [])->assertForbidden();
    $this->delete(route('admin.testimonials.destroy', $testimonial))->assertForbidden();
    $this->assertModelExists($testimonial);
});
