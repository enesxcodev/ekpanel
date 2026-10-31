<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentCategory;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

test('documents can be created and updated without an attached file', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $language = Language::query()->where('code', 'tr')->firstOrFail();
    $category = DocumentCategory::factory()->create();
    $this->actingAs($admin, 'admin');

    $response = $this->post(route('admin.documents.store'), [
        'lang_id' => $language->id,
        'category_id' => $category->id,
        'name' => 'Ürün kataloğu',
        'slug' => 'urun-katalogu',
        'content' => '<p>Güncel katalog</p><script>alert(1)</script>',
        'status' => 'published',
    ])->assertSessionHasNoErrors();

    $document = Document::query()->sole();
    $response->assertRedirectToRoute('admin.documents.edit', ['document' => $document->id, 'lang' => 'tr']);
    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'category_id' => $category->id,
        'status' => 'published',
    ]);
    $this->assertDatabaseHas('document_translations', [
        'document_id' => $document->id,
        'lang_id' => $language->id,
        'name' => 'Ürün kataloğu',
        'slug' => 'urun-katalogu',
        'content' => '<p>Güncel katalog</p>',
    ]);

    $this->put(route('admin.documents.update', $document), [
        'lang_id' => $language->id,
        'category_id' => $category->id,
        'name' => 'Yeni ürün kataloğu',
        'slug' => 'yeni-urun-katalogu',
        'content' => 'Yeni içerik',
        'status' => 'draft',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.documents.edit', ['document' => $document->id, 'lang' => 'tr']);

    $this->assertDatabaseHas('documents', ['id' => $document->id, 'status' => 'draft']);
    $this->assertDatabaseHas('document_translations', ['document_id' => $document->id, 'lang_id' => $language->id, 'name' => 'Yeni ürün kataloğu', 'slug' => 'yeni-urun-katalogu']);
});
