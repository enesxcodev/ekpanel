<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

test('an empty FAQ answer is still rejected by server validation', function (): void {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->postJson(route('admin.faqs.store'), ['name' => 'Bir soru', 'content' => ''])
        ->assertUnprocessable()->assertJsonValidationErrors('content');
    $this->assertDatabaseCount('faqs', 0);
});

test('frequently asked questions can be created and updated in a category', function (): void {
    $admin = Admin::factory()->superAdmin()->create();
    $category = FaqCategory::factory()->create();
    $languageId = Language::query()->where('code', 'tr')->firstOrFail()->id;
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.faqs.store'), [
        'lang_id' => $languageId,
        'category_id' => $category->id,
        'name' => 'Nasıl sipariş veririm?',
        'content' => '<p>Web sitemizden sipariş verebilirsiniz.</p><script>alert(1)</script>',
    ])->assertSessionHasNoErrors();

    $faq = Faq::query()->sole();
    $this->assertDatabaseHas('faqs', [
        'id' => $faq->id,
        'category_id' => $category->id,
    ]);
    $this->assertDatabaseHas('faq_translations', ['faq_id' => $faq->id, 'lang_id' => $languageId, 'name' => 'Nasıl sipariş veririm?', 'content' => '<p>Web sitemizden sipariş verebilirsiniz.</p>']);

    $this->put(route('admin.faqs.update', $faq), [
        'lang_id' => $languageId,
        'category_id' => $category->id,
        'name' => 'Siparişi nasıl veririm?',
        'content' => 'Ürün sayfasını ziyaret edin.',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('faq_translations', ['faq_id' => $faq->id, 'lang_id' => $languageId, 'name' => 'Siparişi nasıl veririm?', 'content' => 'Ürün sayfasını ziyaret edin.']);
});
