<?php

use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;
use Modules\Faqs\Services\FaqsFrontendService;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $disk = Storage::fake('faqs-translations-statuses');
    $disk->put('statuses.json', json_encode(['Faqs' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('FAQ translations remain independent and frontend reads only the requested active language', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    Language::factory()->inactive()->create(['code' => 'fr']);
    $faq = Faq::factory()->uncategorized()->create();
    $turkish = $faq->translations()->where('lang_id', Language::query()->where('code', 'tr')->firstOrFail()->id)->firstOrFail();
    $faq->translations()->create(['lang_id' => $english->id, 'name' => 'How do I order?', 'content' => '<p>Use our website.</p>', 'extra_fields' => ['label' => 'English']]);

    $service = app(FaqsFrontendService::class);

    expect($service->find($faq->id, 'en')?->selectedTranslation->name)->toBe('How do I order?')
        ->and($service->find($faq->id, 'en')?->selectedTranslation->content)->toBe('<p>Use our website.</p>')
        ->and($service->find($faq->id, 'fr'))->toBeNull()
        ->and($faq->translations()->where('lang_id', $turkish->lang_id)->firstOrFail()->name)->toBe($turkish->name);
});

test('missing category translation does not leak the Turkish category name', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $category = FaqCategory::factory()->create();
    $faq = Faq::factory()->for($category, 'category')->create();
    $faq->translations()->create(['lang_id' => $english->id, 'name' => 'English question', 'content' => 'English answer']);

    $localized = app(FaqsFrontendService::class)->find($faq->id, 'en');

    expect($localized?->category?->name)->toBeNull();
});

test('category names are unique within a language and may repeat in another language', function (): void {
    $english = Language::factory()->create(['code' => 'en']);
    $this->withoutVite()->actingAs(Admin::factory()->superAdmin()->create(), 'admin');

    $this->post(route('admin.faqs.categories.store'), ['lang_id' => $english->id, 'name' => 'Support'])
        ->assertSessionHasNoErrors();
    $turkishId = Language::query()->where('code', 'tr')->firstOrFail()->id;
    $this->post(route('admin.faqs.categories.store'), ['lang_id' => $turkishId, 'name' => 'Support'])
        ->assertSessionHasNoErrors();
    $this->post(route('admin.faqs.categories.store'), ['lang_id' => $english->id, 'name' => 'Support'])
        ->assertSessionHasErrors('name');
});
