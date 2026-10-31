<?php

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Settings\Language;
use App\Services\Media\MediaService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;

uses(LazilyRefreshDatabase::class);

test('blog translations keep language data separate on the same content identity', function () {
    $turkish = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
    $english = Language::query()->firstOrCreate(['code' => 'en'], ['is_active' => true]);
    $content = BlogContent::factory()->create();
    $content->translations()->updateOrCreate(['lang_id' => $turkish->id], ['title' => 'Türkçe başlık', 'slug' => 'baslik', 'extra_fields' => ['summary' => 'Türkçe özet']]);
    $translation = $content->translations()->create(['lang_id' => $english->id, 'title' => 'English title', 'slug' => 'title', 'extra_fields' => ['summary' => 'English summary']]);

    $translation->update(['title' => 'Updated title']);

    expect($translation->language->code)->toBe('en');
    expect($translation->blogContent->id)->toBe($content->id);
    $this->assertDatabaseHas('blog_content_translations', ['blog_content_id' => $content->id, 'lang_id' => $turkish->id, 'title' => 'Türkçe başlık']);
    expect($content->translations()->where('lang_id', $turkish->id)->sole()->extra_fields)->toBe(['summary' => 'Türkçe özet']);
});

test('category translations share one category tree identity', function () {
    $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
    $category = BlogCategory::factory()->create();
    $translation = $category->translations()->updateOrCreate(['lang_id' => $language->id], ['name' => 'Kategori', 'slug' => 'kategori']);

    expect($translation->blogCategory->id)->toBe($category->id);
    expect($translation->language->id)->toBe($language->id);
    $this->assertDatabaseCount('blog_category_translations', 1);
});

test('public language pages require translated category ancestry and reject inactive languages', function () {
    $this->withoutVite();
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $root = BlogCategory::factory()->create();
    $child = BlogCategory::factory()->create(['parent_id' => $root->id]);
    $content = BlogContent::factory()->create(['blog_category_id' => $child->id, 'status' => 'published']);
    $content->translations()->create(['lang_id' => $english->id, 'title' => 'English article', 'slug' => 'english-article']);
    $child->translations()->create(['lang_id' => $english->id, 'name' => 'Child category', 'slug' => 'child']);

    $this->get(route('blog.localized.show', ['lang' => 'en', 'slug' => 'english-article']))->assertNotFound();
    $this->get(route('blog.localized.index', ['lang' => 'en']))->assertOk()->assertDontSee('English article');
    $root->translations()->create(['lang_id' => $english->id, 'name' => 'Root category', 'slug' => 'root']);
    $this->get(route('blog.localized.show', ['lang' => 'en', 'slug' => 'english-article']))->assertOk()->assertSee('English article')->assertSee('Root category / Child category');
    $this->get(route('blog.localized.index', ['lang' => 'en']))->assertOk()->assertSee('/en/blog/english-article');
    $english->update(['is_active' => false]);
    $this->get(route('blog.localized.index', ['lang' => 'en']))->assertNotFound();
    $this->get(route('blog.localized.index', ['lang' => 'xx']))->assertNotFound();
    $this->get(route('blog.localized.index', ['lang' => 'tr']))->assertRedirectToRoute('blog.public.index');
});

test('saving another language preserves the original translation and form does not copy its title', function () {
    $this->withoutVite();
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $content = BlogContent::factory()->create(['title' => 'Original Turkish title', 'slug' => 'original', 'extra_fields' => ['yazarin_ozeti' => 'Original summary']]);
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin')->get(route('admin.blog.edit', ['blog' => $content->id, 'lang' => 'en']))->assertOk()->assertDontSee('value="Original Turkish title"', false);
    $this->put(route('admin.blog.update', $content), ['lang_id' => $english->id, 'blog_category_id' => $content->blog_category_id, 'title' => 'English title', 'slug' => 'original', 'status' => 'draft', 'extra_fields' => ['yazarin_ozeti' => 'English summary']])
        ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.blog.edit', ['blog' => $content->id, 'lang' => 'en']);
    $this->assertDatabaseHas('blog_content_translations', ['blog_content_id' => $content->id, 'title' => 'Original Turkish title']);
    $this->assertDatabaseHas('blog_content_translations', ['blog_content_id' => $content->id, 'lang_id' => $english->id, 'title' => 'English title']);
    expect($content->translations()->where('lang_id', $english->id)->sole()->extra_fields)->toBe(['yazarin_ozeti' => 'English summary']);
    expect($content->translations()->where('lang_id', '!=', $english->id)->sole()->extra_fields)->toBe(['yazarin_ozeti' => 'Original summary']);
    $english->update(['is_active' => false]);
    $this->put(route('admin.blog.update', $content), ['lang_id' => $english->id, 'blog_category_id' => $content->blog_category_id, 'title' => 'Rejected', 'slug' => 'original', 'status' => 'draft'])->assertSessionHasErrors('lang_id');
});

test('duplicate slugs in the same language are rejected without creating a content', function () {
    $existing = BlogContent::factory()->create(['slug' => 'reserved']);
    $language = Language::query()->where('code', 'tr')->sole();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->post(route('admin.blog.store'), ['lang_id' => $language->id, 'blog_category_id' => $existing->blog_category_id, 'title' => 'Duplicate', 'slug' => 'reserved', 'status' => 'draft'])
        ->assertSessionHasErrors('slug');
    $this->assertDatabaseCount('blog_contents', 1);
});

test('forms disable saving when no language is active', function () {
    $this->withoutVite();
    Language::query()->update(['is_active' => false]);
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.blog.create'))->assertOk()->assertSee('<fieldset disabled>', false);
});

test('translation media links open the parent in its language and deleting content detaches every translation', function () {
    $english = Language::query()->create(['code' => 'en', 'is_active' => true]);
    $content = BlogContent::factory()->create();
    $translation = $content->translations()->create(['lang_id' => $english->id, 'title' => 'English', 'slug' => 'english']);
    $media = MediaFile::factory()->create();
    $mediaService = app(MediaService::class);
    foreach ($content->translations()->get() as $item) {
        $mediaService->attachUsage($media, $item, 'extra_fields.document', 'Document', 'admin.blog.translations.edit');
    }
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->get(route('admin.blog.translations.edit', $translation))
        ->assertRedirectToRoute('admin.blog.edit', ['blog' => $content->id, 'lang' => 'en']);

    $this->delete(route('admin.blog.destroy', $content))->assertRedirectToRoute('admin.blog.index');

    $this->assertDatabaseCount('blog_content_translations', 0);
    $this->assertDatabaseCount('media_usages', 0);
    $this->assertModelExists($media);
});
