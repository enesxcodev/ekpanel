<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentCategory;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

test('documents can be created and updated without an attached file', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $category = DocumentCategory::factory()->create();
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.documents.store'), [
        'category_id' => $category->id,
        'name' => 'Ürün kataloğu',
        'slug' => 'urun-katalogu',
        'content' => '<p>Güncel katalog</p><script>alert(1)</script>',
        'status' => 'published',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.documents.index');

    $document = Document::query()->sole();
    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'category_id' => $category->id,
        'name' => 'Ürün kataloğu',
        'slug' => 'urun-katalogu',
        'content' => '<p>Güncel katalog</p>',
        'status' => 'published',
    ]);

    $this->put(route('admin.documents.update', $document), [
        'category_id' => $category->id,
        'name' => 'Yeni ürün kataloğu',
        'slug' => 'yeni-urun-katalogu',
        'content' => 'Yeni içerik',
        'status' => 'draft',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.documents.index');

    $this->assertDatabaseHas('documents', ['id' => $document->id, 'name' => 'Yeni ürün kataloğu', 'status' => 'draft']);
});
