<?php

namespace App\Services\Frontend;

use App\Models\Forms\SiteForm;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

class HomePageService
{
    public function __construct(private SettingsService $settings) {}

    /** @return array<string, mixed> */
    public function index(): array
    {
        $view['slider'] = $this->slider();
        $view['advantages'] = $this->advantages();
        $view['brands'] = $this->brands();
        $view['services'] = $this->services();
        $view['sectors'] = $this->sectors();
        $view['about'] = $this->about();
        $view['products'] = $this->products();
        $view['productCategories'] = $this->productCategories($view['products']);
        $view['projects'] = $this->projects();
        $view['testimonials'] = $this->testimonials();
        $view['resources'] = $this->resources();
        $view['opportunities'] = $this->opportunities();
        $view['faqs'] = $this->faqs();
        $view['navigation'] = $this->menus();
        $view['site'] = $this->site();
        $view['copy'] = $this->copy();
        $view['footerCorporate'] = $this->menu('footer_corporate');
        $view['footerExplore'] = $this->menu('footer_explore');
        $view['seoTitle'] = $this->settings->get('site.meta_title') ?: $view['site']['name'];
        $view['contactFormKey'] = $this->contactFormKey();
        $view['cartUrl'] = $this->enabled('Store') && Route::has('store.cart') ? route('store.cart') : null;
        $view['appointmentAvailable'] = $this->enabled('Appointments');

        return $this->finalizeView($view);
    }

    /**
     * @param  array<string, mixed>  $view
     * @return array<string, mixed>
     */
    private function finalizeView(array $view): array
    {
        $view['contactAvailable'] = $view['contactFormKey'] !== null || filled($view['site']['phone']) || filled($view['site']['email']);
        $view['targets'] = $this->availableTargets($view);
        foreach (['navigation', 'footerCorporate', 'footerExplore'] as $key) {
            $view[$key] = $this->visibleMenu($view[$key], $view['targets']);
        }
        $view['slider'] = $this->filterActionTargets($view['slider'], $view['targets']);
        $view['copy'] = $this->filterActionTargets($view['copy'], $view['targets']);

        return $view;
    }

    /**
     * @param  array<string, mixed>  $view
     * @return array<string, bool>
     */
    private function availableTargets(array $view): array
    {
        return [
            '#anasayfa' => true,
            '#kurumsal' => filled($view['about']['title']) || $view['about']['paragraphs'] !== [] || $view['about']['values'] !== [],
            '#degerlerimiz' => $view['about']['values'] !== [],
            '#hizmetler' => $view['services']->isNotEmpty() || $view['sectors']->isNotEmpty(),
            '#urunler' => $view['products']->isNotEmpty(),
            '#projeler' => $view['projects']->isNotEmpty(),
            '#bilgi-merkezi' => $view['resources']->isNotEmpty(),
            '#sss' => $view['faqs']->isNotEmpty(),
            '#iletisim' => $view['contactAvailable'],
            '#iletisim-formu' => $view['contactFormKey'] !== null,
        ];
    }

    /**
     * @param  array<string, mixed>  $content
     * @param  array<string, bool>  $targets
     * @return array<string, mixed>
     */
    private function filterActionTargets(array $content, array $targets): array
    {
        foreach ($content as $key => $value) {
            if (str_ends_with($key, '_url') && str_starts_with($value ?? '', '#') && ! ($targets[$value] ?? false)) {
                $content[$key] = null;
            }
        }

        return $content;
    }

    /** @return array<string, mixed> */
    private function slider(): array
    {
        $slider = $this->service('Slider')?->getFirst();

        return [
            'eyebrow' => $this->text('hero_eyebrow'),
            'title' => $slider?->title,
            'description' => $slider === null ? null : (data_get($slider->extra_fields, 'short_description') ?: $slider->subtitle),
            'image' => $slider?->image_url,
            'primary_label' => $this->text('hero_primary_label'),
            'primary_url' => $this->safeUrl($this->text('hero_primary_url')),
            'secondary_label' => $this->text('hero_secondary_label'),
            'secondary_url' => $this->safeUrl($this->text('hero_secondary_url')),
        ];
    }

    /** @return list<array<string, string>> */
    private function advantages(): array
    {
        return collect(range(1, 3))->map(fn (int $number): array => [
            'title' => $this->text('hero_advantage_'.$number.'_label'),
            'icon' => $this->text('hero_advantage_'.$number.'_icon'),
        ])->filter(fn (array $item): bool => filled($item['title']))->values()->all();
    }

    /** @return array<string, mixed> */
    private function about(): array
    {
        return [
            'title' => $this->text('about_title'),
            'paragraphs' => collect(range(1, 2))->map(fn (int $number): string => $this->text('about_paragraph_'.$number))->filter()->values()->all(),
            'values' => collect(range(1, 4))->map(fn (int $number): array => [
                'title' => $this->text('value_'.$number.'_title'),
                'description' => $this->text('value_'.$number.'_description'),
                'icon' => $this->text('value_'.$number.'_icon'),
            ])->filter(fn (array $value): bool => filled($value['title']))->values()->all(),
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    private function services(): Collection
    {
        return $this->records('Services')->take(3)->map(fn (Model $item): array => [
            'id' => $item->id, 'title' => $item->title, 'description' => $item->short_description,
            'icon' => $item->icon ?: 'briefcase', 'detail' => $this->plainText($item->content),
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function brands(): Collection
    {
        return $this->records('Brand')->take(5)->map(fn (Model $item): array => [
            'name' => $item->name, 'image' => $item->image_url, 'url' => $this->safeUrl($item->link),
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function sectors(): Collection
    {
        return $this->records('Sector')->take(6)->map(fn (Model $item): array => [
            'title' => $item->title, 'icon' => 'briefcase',
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function projects(): Collection
    {
        $languageCode = request()->attributes->get('pages_language') ?? 'tr';

        return ($this->service('Projects')?->getAll(null, $languageCode) ?? collect())->take(3)->map(fn (Model $item): array => [
            'id' => $item->id, 'title' => $item->selectedTranslation->title, 'category' => $item->category?->selectedTranslation?->name,
            'image' => $item->cover_url, 'description' => Str::limit($this->plainText($item->selectedTranslation->content), 240),
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function testimonials(): Collection
    {
        $languageCode = request()->attributes->get('pages_language') ?? request()->route('lang');

        return ($this->service('Testimonials')?->getAll($languageCode) ?? collect())->take(2)->map(fn (Model $item): array => [
            'name' => $item->selectedTranslation->customer_name, 'company' => $item->selectedTranslation->company,
            'initials' => Str::upper(collect(explode(' ', trim($item->selectedTranslation->customer_name)))->filter()->take(2)->map(fn (string $word): string => mb_substr($word, 0, 1))->implode('')),
            'content' => $this->plainText($item->selectedTranslation->content),
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function faqs(): Collection
    {
        $languageCode = request()->attributes->get('pages_language') ?? request()->route('lang');

        return ($this->service('Faqs')?->getAll(null, $languageCode) ?? collect())->take(4)->map(fn (Model $item): array => [
            'question' => $item->selectedTranslation->name, 'answer' => $this->plainText($item->selectedTranslation->content),
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function opportunities(): Collection
    {
        $cards = collect();
        $branch = $this->service('Branches')?->getFirst();
        if ($branch !== null) {
            $cards->push(['title' => $branch->name, 'subtitle' => $branch->category?->name, 'description' => $branch->address_detail, 'icon' => 'map-pin', 'url' => $this->safeUrl($branch->map_url)]);
        }
        foreach (['Events' => ['Event', 'events'], 'Careers' => ['JobPosting', 'job_postings']] as $module => [$model, $table]) {
            $class = 'Modules\\'.$module.'\\Models\\'.$model;
            if (! $this->enabled($module) || ! class_exists($class) || ! Schema::hasTable($table)) {
                continue;
            }
            $query = $class::query()->where('is_active', true);
            if ($module === 'Careers') {
                $query->where(fn (Builder $query): Builder => $query->whereNull('application_deadline')->orWhereDate('application_deadline', '>=', today()));
            }
            $item = $query->first();
            if ($item !== null) {
                $cards->push(['title' => $item->title, 'subtitle' => $module === 'Events' ? $item->venue : $item->location, 'description' => $this->plainText($item->description), 'icon' => $module === 'Events' ? 'calendar' : 'briefcase', 'url' => null]);
            }
        }

        return $cards;
    }

    /** @return list<array<string, mixed>> */
    private function menus(): array
    {
        return $this->menu((string) config('menus.main_menu', 'main'));
    }

    /** @return list<array<string, mixed>> */
    private function menu(string $key): array
    {
        return $this->service('Menus')?->getTree($key) ?? [];
    }

    /** @param list<array<string, mixed>> $items
     * @param  array<string, bool>  $targets
     * @return list<array<string, mixed>>
     */
    private function visibleMenu(array $items, array $targets): array
    {
        return collect($items)->map(function (array $item) use ($targets): ?array {
            $item['children'] = $this->visibleMenu($item['children'] ?? [], $targets);
            $url = $this->safeUrl($item['url'] ?? null);
            if ($url !== null && str_starts_with($url, '#') && ! ($targets[$url] ?? false)) {
                $url = null;
            }
            $item['url'] = $url;

            return $url !== null || $item['children'] !== [] ? $item : null;
        })->filter()->values()->all();
    }

    private function contactFormKey(): ?string
    {
        if (! Schema::hasTable('site_forms')) {
            return null;
        }

        return SiteForm::query()->where('key', 'default-iletisim')
            ->where('is_active', true)->whereHas('fields')->value('key');
    }

    private function enabled(string $module): bool
    {
        return Module::find($module)?->isEnabled() ?? false;
    }

    private function service(string $module): ?object
    {
        $class = 'Modules\\'.$module.'\\Services\\'.$module.'FrontendService';

        return $this->enabled($module) && class_exists($class) ? app($class) : null;
    }

    /** @return Collection<int, Model> */
    private function records(string $module): Collection
    {
        return $this->service($module)?->getAll() ?? collect();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function products(): Collection
    {
        $catalog = $this->service('Products');
        if ($catalog === null || ! Schema::hasTable('products') || ! Schema::hasTable('product_categories')) {
            return collect();
        }
        $products = $catalog->products(limit: 12);

        $cards = $products->map(fn (Model $item): array => [
            'id' => $item->id, 'title' => $item->title, 'description' => $item->short_description,
            'category' => $item->category?->name, 'category_id' => (string) ($item->product_category_id ?? 'other'),
            'icon' => 'box', 'image' => $item->image_url,
        ]);

        return $cards;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $products
     * @return Collection<int, array{id: string, name: string}>
     */
    private function productCategories(Collection $products): Collection
    {
        return $products->filter(fn (array $product): bool => filled($product['category']))->unique('category_id')->map(fn (array $product): array => [
            'id' => $product['category_id'], 'name' => $product['category'],
        ])->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function resources(): Collection
    {
        $cards = collect();
        $languageCode = request()->attributes->get('pages_language') ?? request()->route('lang');
        foreach (['blog' => 'Blog', 'documents' => 'Documents', 'gallery' => 'Galeriler'] as $key => $module) {
            $service = $this->service($module);
            $item = $module === 'Documents'
                ? $service?->getFirst(null, $languageCode)
                : $service?->getFirst();
            if ($item === null) {
                continue;
            }
            $title = $module === 'Documents' ? $item->selectedTranslation?->name : ($item->title ?: $item->name);
            $description = $module === 'Documents'
                ? $item->selectedTranslation?->content
                : ($item->short_description ?: $item->description ?: $item->content);
            $cards->push([
                'title' => $title,
                'description' => Str::limit($this->plainText($description), 130),
                'icon' => match ($key) {
                    'documents' => 'file-text', 'gallery' => 'photo', default => 'book'
                },
                'label' => $this->text('resources_'.$key.'_label'),
                'action' => $this->text('resources_action_label'),
                'url' => $key === 'documents' ? $this->safeUrl($item->file_url) : null,
            ]);
        }

        return $cards;
    }

    /** @return array<string, mixed> */
    private function site(): array
    {
        $phone = (string) $this->settings->get('contact.phone', '');

        return [
            'name' => $this->settings->get('site.company_name', ''),
            'description' => $this->settings->get('site.description', ''),
            'logo' => $this->settings->mediaUrl('site.logo'), 'footer_logo' => $this->settings->mediaUrl('site.footer_logo'),
            'phone' => $phone, 'phone_url' => 'tel:'.preg_replace('/[^+0-9]/', '', $phone),
            'email' => $this->settings->get('contact.email', ''),
            'address' => $this->settings->get('contact.address', ''),
            'socials' => collect(['facebook', 'instagram', 'linkedin', 'youtube'])->mapWithKeys(fn (string $network): array => [$network => $this->safeUrl($this->settings->get('social.'.$network))])->filter()->all(),
        ];
    }

    private function text(string $key): string
    {
        return (string) $this->settings->get('default_home.'.$key, '');
    }

    /** @return array<string, string|null> */
    private function copy(): array
    {
        return collect(['about_eyebrow', 'about_action_label', 'about_action_url', 'values_action_label', 'values_action_url', 'brands_title', 'services_title', 'services_action_label', 'services_action_url', 'services_detail_label', 'sectors_title', 'products_eyebrow', 'products_title', 'products_action_label', 'products_action_url', 'products_item_action_label', 'products_all_label', 'projects_eyebrow', 'projects_title', 'projects_action_label', 'projects_action_url', 'projects_detail_label', 'testimonials_eyebrow', 'testimonials_title', 'resources_eyebrow', 'resources_title', 'resources_blog_label', 'resources_documents_label', 'resources_gallery_label', 'resources_action_label', 'opportunities_eyebrow', 'opportunities_action_label', 'faqs_eyebrow', 'faqs_title', 'faqs_description', 'faqs_action_label', 'faqs_action_url', 'contact_eyebrow', 'contact_title', 'contact_description', 'contact_appointment_label', 'footer_corporate_title', 'footer_explore_title', 'footer_contact_title', 'footer_copyright', 'footer_back_top_label', 'header_action_label', 'header_action_url'])->mapWithKeys(fn (string $key): array => [$key => str_ends_with($key, '_url') ? $this->safeUrl($this->text($key)) : $this->text($key)])->all();
    }

    private function plainText(?string $value): string
    {
        return trim(html_entity_decode(strip_tags($value ?? ''), ENT_QUOTES, 'UTF-8'));
    }

    private function safeUrl(?string $url): ?string
    {
        if ($url === null || $url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) {
            return null;
        }
        $decoded = rawurldecode($url);
        if (preg_match('/[\x00-\x1f\x7f\\\\]/', $decoded) || str_starts_with($decoded, '//')) {
            return null;
        }
        if (preg_match('~^mailto:([^?]+)(?:\?[^\s]*)?$~i', $url, $matches) && filter_var($matches[1], FILTER_VALIDATE_EMAIL)) {
            return $url;
        }
        if (preg_match('~^tel:\+?[0-9().-]+$~i', $url)) {
            return $url;
        }
        if (preg_match('~^https?://~i', $url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return preg_match('~^(?:/(?!/)|\#[a-zA-Z])[a-zA-Z0-9_./?&=%\#:+-]*$~D', $url) ? $url : null;
    }
}
