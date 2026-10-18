<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

test('frequently asked questions can be created and updated in a category', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $category = FaqCategory::factory()->create();
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.faqs.store'), [
        'category_id' => $category->id,
        'name' => 'Nasıl sipariş veririm?',
        'content' => '<p>Web sitemizden sipariş verebilirsiniz.</p><script>alert(1)</script>',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.faqs.index');

    $faq = Faq::query()->sole();
    $this->assertDatabaseHas('faqs', [
        'id' => $faq->id,
        'category_id' => $category->id,
        'name' => 'Nasıl sipariş veririm?',
        'content' => '<p>Web sitemizden sipariş verebilirsiniz.</p>',
    ]);

    $this->put(route('admin.faqs.update', $faq), [
        'category_id' => $category->id,
        'name' => 'Siparişi nasıl veririm?',
        'content' => 'Ürün sayfasını ziyaret edin.',
    ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.faqs.index');

    $this->assertDatabaseHas('faqs', ['id' => $faq->id, 'name' => 'Siparişi nasıl veririm?', 'content' => 'Ürün sayfasını ziyaret edin.']);
});
