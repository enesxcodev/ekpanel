<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Blog\Database\Seeders\BlogDatabaseSeeder;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Modules\Blog\Models\BlogTag;
use Spatie\Permission\Models\Permission;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

test('blog content category and tag actions notify all admins with links to the affected record', function (): void {
    $permissionNames = [
        'blog.create', 'blog.update', 'blog.delete',
        'blog.categories.create', 'blog.categories.update', 'blog.categories.delete',
        'blog.tags.create', 'blog.tags.update', 'blog.tags.delete',
    ];
    $actor = Admin::factory()->create();
    $recipient = Admin::factory()->create();
    $superAdmin = Admin::factory()->superAdmin()->create();
    $actor->givePermissionTo(collect($permissionNames)->map(fn (string $name): Permission => Permission::findOrCreate($name, 'admin'))->all());
    $this->actingAs($actor, 'admin');

    $assertNotification = function (string $title, string $link) use ($recipient): void {
        $notification = $recipient->notifications()->where('data->title', $title)->firstOrFail();
        expect($notification->data['link'])->toBe($link);
    };

    $category = BlogCategory::factory()->create();
    $this->post(route('admin.blog.store'), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id,
        'blog_category_id' => $category->id,
        'title' => 'Yeni yazı',
        'slug' => 'yeni-yazi',
        'content' => '<p>İçerik</p>',
        'status' => 'draft',
    ])->assertRedirect();
    $blog = BlogContent::query()->sole();
    $assertNotification('Blog yazısı eklendi', route('admin.blog.edit', ['blog' => $blog->id, 'lang' => 'tr'], absolute: false));

    $this->put(route('admin.blog.update', $blog), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id,
        'blog_category_id' => $category->id,
        'title' => 'Güncel yazı',
        'slug' => 'guncel-yazi',
        'content' => '<p>Güncel içerik</p>',
        'status' => 'draft',
    ])->assertRedirect();
    $assertNotification('Blog yazısı güncellendi', route('admin.blog.edit', ['blog' => $blog->id, 'lang' => 'tr'], absolute: false));

    $this->delete(route('admin.blog.destroy', $blog))->assertRedirect();
    $assertNotification('Blog yazısı silindi', route('admin.blog.index', absolute: false));

    $this->post(route('admin.blog.categories.store'), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id, 'name' => 'Yeni kategori', 'slug' => 'yeni-kategori', 'sort_order' => 0])
        ->assertRedirect();
    $createdCategory = BlogCategory::query()->whereHas('translations', fn ($query) => $query->where('slug', 'yeni-kategori'))->sole();
    $assertNotification('Blog kategorisi eklendi', route('admin.blog.categories.edit', ['category' => $createdCategory->id, 'lang' => 'tr'], absolute: false));

    $this->put(route('admin.blog.categories.update', $createdCategory), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id, 'name' => 'Güncel kategori', 'slug' => 'guncel-kategori', 'sort_order' => 0])
        ->assertRedirect();
    $assertNotification('Blog kategorisi güncellendi', route('admin.blog.categories.edit', ['category' => $createdCategory->id, 'lang' => 'tr'], absolute: false));

    $this->delete(route('admin.blog.categories.destroy', $createdCategory))->assertRedirect();
    $assertNotification('Blog kategorisi silindi', route('admin.blog.categories.index', absolute: false));

    $this->post(route('admin.blog.tags.store'), ['name' => 'Yeni etiket', 'slug' => 'yeni-etiket'])->assertRedirectToRoute('admin.blog.tags.index');
    $tag = BlogTag::query()->where('slug', 'yeni-etiket')->sole();
    $assertNotification('Blog etiketi eklendi', route('admin.blog.tags.edit', ['tag' => $tag->id], absolute: false));

    $this->put(route('admin.blog.tags.update', $tag), ['name' => 'Güncel etiket', 'slug' => 'guncel-etiket'])->assertRedirectToRoute('admin.blog.tags.index');
    $assertNotification('Blog etiketi güncellendi', route('admin.blog.tags.edit', ['tag' => $tag->id], absolute: false));

    $this->delete(route('admin.blog.tags.destroy', $tag))->assertRedirectToRoute('admin.blog.tags.index');
    $assertNotification('Blog etiketi silindi', route('admin.blog.tags.index', absolute: false));

    expect($actor->notifications()->count())->toBe(9)
        ->and($recipient->notifications()->count())->toBe(9)
        ->and($superAdmin->notifications()->count())->toBe(9);
});

test('content can be created, updated and published from one category with multiple tags', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $category = BlogCategory::factory()->create();
    $tags = BlogTag::factory()->count(2)->create();
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.blog.store'), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id,
        'blog_category_id' => $category->id,
        'title' => 'İçerik başlığı',
        'slug' => 'icerik-basligi',
        'tags' => $tags->modelKeys(),
        'content' => '<p>Güvenli içerik</p><script>alert(1)</script>',
    ])->assertRedirect();

    $content = BlogContent::query()->sole();
    expect($content->status)->toBe('draft')->and($content->tags)->toHaveCount(2);
    $this->assertDatabaseHas('blog_content_translations', ['blog_content_id' => $content->id, 'content' => '<p>Güvenli içerik</p>']);
    $this->assertDatabaseHas('blog_content_tag', ['blog_content_id' => $content->id, 'blog_tag_id' => $tags[0]->id]);

    $this->put(route('admin.blog.update', $content), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id,
        'blog_category_id' => $category->id,
        'title' => 'Yayına alınan içerik',
        'slug' => 'yayinlanan-icerik',
        'status' => 'published',
        'published_at' => now()->format('Y-m-d\\TH:i'),
        'tags' => [$tags[1]->id],
    ])->assertRedirect();

    $this->assertDatabaseHas('blog_contents', ['id' => $content->id, 'status' => 'published']);
    $this->assertDatabaseHas('blog_content_translations', ['blog_content_id' => $content->id, 'slug' => 'yayinlanan-icerik']);
    $this->get(route('blog.public.show', 'yayinlanan-icerik'))->assertOk()->assertSee('Yayına alınan içerik');
});

test('category tables include relation counts when sorted by a count column', function (string $column): void {
    $admin = Admin::factory()->superAdmin()->create();
    $parent = BlogCategory::factory()->create();
    $child = BlogCategory::factory()->for($parent, 'parent')->create();
    BlogContent::factory()->for($parent, 'category')->create();

    $this->actingAs($admin, 'admin')->getJson(route('admin.blog.categories.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'columns' => [['data' => $column, 'name' => $column, 'orderable' => 'true', 'searchable' => 'false']],
        'order' => [['column' => 0, 'dir' => 'desc']],
    ]))->assertOk()
        ->assertJsonPath('recordsTotal', 2)
        ->assertJsonPath('data.0.id', $parent->id)
        ->assertJsonPath('data.0.children_count', 1)
        ->assertJsonPath('data.0.contents_count', 1)
        ->assertJsonPath('data.1.id', $child->id)
        ->assertJsonPath('data.1.children_count', 0)
        ->assertJsonPath('data.1.contents_count', 0);
})->with(['children_count', 'contents_count']);

test('tag tables include content counts and can sort by them', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $usedTag = BlogTag::factory()->create();
    $emptyTag = BlogTag::factory()->create();
    BlogContent::factory()->hasAttached($usedTag, [], 'tags')->create();

    $this->actingAs($admin, 'admin')->getJson(route('admin.blog.tags.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'columns' => [['data' => 'contents_count', 'name' => 'contents_count', 'orderable' => 'true', 'searchable' => 'false']],
        'order' => [['column' => 0, 'dir' => 'desc']],
    ]))->assertOk()
        ->assertJsonPath('recordsTotal', 2)
        ->assertJsonPath('data.0.id', $usedTag->id)
        ->assertJsonPath('data.0.contents_count', 1)
        ->assertJsonPath('data.1.id', $emptyTag->id)
        ->assertJsonPath('data.1.contents_count', 0);
});

test('future publication dates are rejected', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $category = BlogCategory::factory()->create();
    $this->actingAs($admin, 'admin')->post(route('admin.blog.store'), [
        'blog_category_id' => $category->id,
        'title' => 'İleri tarihli içerik',
        'slug' => 'ileri-tarihli-icerik',
        'status' => 'published',
        'published_at' => now()->addDay()->format('Y-m-d\\TH:i'),
    ])->assertSessionHasErrors('published_at');
});

test('category filters include contents from nested categories', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $root = BlogCategory::factory()->create();
    $child = BlogCategory::factory()->create(['parent_id' => $root->id]);
    BlogContent::factory()->create(['blog_category_id' => $root->id]);
    BlogContent::factory()->create(['blog_category_id' => $child->id]);
    BlogContent::factory()->create();
    $this->actingAs($admin, 'admin');

    $this->getJson(route('admin.blog.data', ['category_id' => $root->id, 'draw' => 1, 'start' => 0, 'length' => 10]))
        ->assertOk()->assertJsonPath('recordsFiltered', 2);
});

test('draft and archived content are unavailable to visitors', function (string $status): void {
    $content = BlogContent::factory()->create(['slug' => $status.'-content', 'status' => $status]);

    $this->get(route('blog.public.show', $content->slug))->assertNotFound();
})->with(['draft', 'archived']);

test('category parent changes cannot create cycles and non-empty categories cannot be deleted', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $parent = BlogCategory::factory()->create();
    $child = BlogCategory::factory()->create(['parent_id' => $parent->id]);
    $content = BlogContent::factory()->create(['blog_category_id' => $child->id]);
    $this->actingAs($admin, 'admin');

    $this->put(route('admin.blog.categories.update', $parent), ['lang_id' => Language::query()->where('code', 'tr')->sole()->id,
        'name' => $parent->name,
        'slug' => $parent->slug,
        'parent_id' => $child->id,
        'sort_order' => 0,
    ])->assertSessionHasErrors('parent_id');

    $this->delete(route('admin.blog.categories.destroy', $child))->assertRedirect();
    $this->assertModelExists($content);
    $this->assertModelExists($child);
});

test('deleting a tag removes its association and preserves the content', function (): void {
    $tag = BlogTag::factory()->create();
    $content = BlogContent::factory()->create();
    $content->tags()->attach($tag);
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');

    $this->delete(route('admin.blog.tags.destroy', $tag))->assertRedirectToRoute('admin.blog.tags.index');

    $this->assertModelExists($content);
    $this->assertDatabaseMissing('blog_content_tag', ['blog_content_id' => $content->id, 'blog_tag_id' => $tag->id]);
});

test('blog seeder creates the three editable root categories idempotently', function (): void {
    $this->seed(BlogDatabaseSeeder::class);
    $this->seed(BlogDatabaseSeeder::class);

    expect(BlogCategory::query()->whereNull('parent_id')->count())->toBe(3);
    $this->assertDatabaseHas('blog_category_translations', ['name' => 'Blog', 'slug' => 'blog']);
    $this->assertDatabaseHas('blog_category_translations', ['name' => 'Haberler', 'slug' => 'haberler']);
    $this->assertDatabaseHas('blog_category_translations', ['name' => 'Duyurular', 'slug' => 'duyurular']);
});

test('blog forms and the shared Pages category picker render', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.blog.create'))->assertOk()->assertSee('data-hugerte', false)->assertSee('data-slug-title', false);
    $this->get(route('admin.blog.categories.create'))->assertOk()->assertSee('Kategori ekle');
    $this->get(route('admin.blog.tags.index'))->assertOk()->assertSee('Etiketler');
    $this->get(route('admin.pages.create'))->assertOk()->assertSee('page-category-search-tree', false);
    $this->get(route('admin.blog.index'))->assertOk()->assertSee('İçerikler')->assertSee('Kategoriler')->assertSee('Etiketler');
});
