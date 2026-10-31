<?php

namespace Modules\Store\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Modules\Store\Models\StoreCategory;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;
use Nwidart\Modules\Facades\Module;

class StoreFrontendService
{
    public function __construct(private StoreLanguageService $languages) {}

    public function available(): bool
    {
        return Module::find('Store')?->isEnabled() ?? false;
    }

    /** @return Builder<StoreProduct> */
    private function query(?int $languageId = null): Builder
    {
        $languageId ??= $this->languages->forFrontend()?->id;

        return StoreProduct::query()->where('is_active', true)->where('is_archived', false)
            ->whereHas('translations', fn (Builder $translation) => $translation->where('lang_id', $languageId))
            ->where(fn (Builder $query) => $query->whereNull('store_category_id')->orWhereHas('category', fn (Builder $category) => $category->where('is_active', true)->whereHas('translations', fn (Builder $translation) => $translation->where('lang_id', $languageId))))
            ->with(['translation', 'category.translation', 'imageMedia', 'shareImageMedia', 'images', 'variants' => fn ($variants) => $variants->where('is_active', true)->where('is_archived', false)->with('imageMedia')])
            ->orderBy('sort_order')->orderBy('id');
    }

    /** @return LengthAwarePaginator<int, StoreProduct> */
    public function products(?int $categoryId = null, ?string $search = null, int $perPage = 12, ?string $languageCode = null): LengthAwarePaginator
    {
        if (! $this->available()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, max(1, min(100, $perPage)));
        }
        $language = $this->languages->forFrontend($languageCode);
        if (! $language) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, max(1, min(100, $perPage)));
        }
        $query = $this->query($language->id);
        if ($categoryId !== null) {
            $query->where('store_category_id', $categoryId);
        }
        if (filled($search)) {
            $query->whereHas('translations', fn (Builder $translation) => $translation->where('lang_id', $language->id)->where('title', 'like', '%'.$search.'%'));
        }

        return $query->paginate(max(1, min(100, $perPage)))->withQueryString();
    }

    /** @return Collection<int, StoreCategory> */
    public function categories(?string $languageCode = null): Collection
    {
        $language = $this->languages->forFrontend($languageCode);

        return $this->available() && $language ? StoreCategory::query()->where('is_active', true)->whereHas('translations', fn (Builder $translation) => $translation->where('lang_id', $language->id))->with('translation')->orderBy('sort_order')->orderBy('id')->get() : new Collection;
    }

    public function findBySlug(string $slug, ?string $languageCode = null): ?StoreProduct
    {
        $language = $this->languages->forFrontend($languageCode);

        return $this->available() && $language ? $this->query($language->id)->whereHas('translations', fn (Builder $translation) => $translation->where('lang_id', $language->id)->where('slug', $slug))->first() : null;
    }

    /** @return array{StoreProduct, ?StoreVariant} */
    public function sellable(int $productId, ?int $variantId, bool $lock = false): array
    {
        $query = $this->query();
        if ($lock) {
            $query->lockForUpdate();
        }
        $product = $query->find($productId);
        if (! $this->available() || ! $product) {
            throw ValidationException::withMessages(['cart' => 'Sepetteki bir ürün artık satışta değil.']);
        }
        $variant = null;
        if ($product->has_variants) {
            $query = StoreVariant::query()->where('store_product_id', $product->id)->where('is_active', true)->where('is_archived', false);
            if ($lock) {
                $query->lockForUpdate();
            }
            $variant = $variantId ? $query->find($variantId) : null;
            if (! $variant) {
                throw ValidationException::withMessages(['variant_id' => 'Geçerli bir ürün varyantı seçin.']);
            }
        } elseif ($variantId !== null) {
            throw ValidationException::withMessages(['variant_id' => 'Bu ürün varyant kullanmıyor.']);
        }

        return [$product, $variant];
    }
}
