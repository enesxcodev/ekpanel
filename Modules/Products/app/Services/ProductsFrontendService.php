<?php

namespace Modules\Products\Services;

use App\Models\Forms\SiteForm;
use App\Services\Forms\FormContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Nwidart\Modules\Facades\Module;

class ProductsFrontendService
{
    public function __construct(private ProductsLanguageService $languages) {}

    public function available(): bool
    {
        return Module::find('Products')?->isEnabled() ?? false;
    }

    /** @return Collection<int, Product> */
    public function products(?int $categoryId = null, ?int $limit = null, ?string $languageCode = null): Collection
    {
        if (! $this->available()) {
            return new Collection;
        }
        if ($this->languages->forFrontend($languageCode) === null) {
            return new Collection;
        }
        $query = $this->visible()->with(['translations', 'category.translations', 'imageMedia', 'images'])->whereHas('translations', fn (Builder $translation): Builder => $translation->where('lang_id', request()->attributes->get('products_language_id')))->orderBy('sort_order')->orderBy('id');
        if ($categoryId !== null) {
            $query->where('product_category_id', $categoryId);
        }
        if ($limit !== null) {
            if ($limit <= 0) {
                return new Collection;
            }
            $query->limit($limit);
        }

        return $query->get();
    }

    /** @return Collection<int, ProductCategory> */
    public function categories(?string $languageCode = null): Collection
    {
        if (! $this->available() || $this->languages->forFrontend($languageCode) === null) {
            return new Collection;
        }

        return ProductCategory::query()->with(['translations'])->where('is_active', true)->whereHas('translations', fn (Builder $translation): Builder => $translation->where('lang_id', request()->attributes->get('products_language_id')))->orderBy('sort_order')->orderBy('id')->get();
    }

    public function findBySlug(string $slug, ?string $languageCode = null): ?Product
    {
        if (! $this->available() || $this->languages->forFrontend($languageCode) === null) {
            return null;
        }

        return $this->visible()->with(['translations', 'category.translations', 'imageMedia', 'images'])->whereHas('translations', fn (Builder $translation): Builder => $translation->where('lang_id', request()->attributes->get('products_language_id'))->where('slug', $slug))->first();
    }

    /** @return array{key: string, context: string}|null */
    public function formFor(Product $product): ?array
    {
        if (! $this->available() || $this->languages->forFrontend() === null) {
            return null;
        }
        $current = $this->visible()->find($product->id);
        if (! $current?->site_form_id) {
            return null;
        }
        $form = SiteForm::query()->where('is_active', true)->find($current->site_form_id);
        if ($form === null || ! $form->fields()->exists()) {
            return null;
        }

        return ['key' => $form->key, 'context' => app(FormContext::class)->issue('product', $current->id, $form)];
    }

    /** @return array{type: string, id: int, title: string, slug: string}|null */
    public function formContext(int $id, SiteForm $form): ?array
    {
        if (! $this->available() || ! $form->is_active || $this->languages->forFrontend() === null) {
            return null;
        }
        $product = $this->visible()->where('site_form_id', $form->id)->whereHas('translations', fn (Builder $translation): Builder => $translation->where('lang_id', request()->attributes->get('products_language_id')))->with('translations')->lockForUpdate()->find($id);

        return $product ? ['type' => 'product', 'id' => $product->id, 'title' => $product->title, 'slug' => $product->slug] : null;
    }

    /** @return Builder<Product> */
    private function visible(): Builder
    {
        $languageId = request()->attributes->get('products_language_id');

        return Product::query()->where('is_active', true)->where(function (Builder $query) use ($languageId): void {
            $query->whereNull('product_category_id')->orWhereHas('category', function (Builder $category) use ($languageId): void {
                $category->where('is_active', true);
                if ($languageId !== null) {
                    $category->whereHas('translations', fn (Builder $translation): Builder => $translation->where('lang_id', $languageId));
                }
            });
        });
    }
}
