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
    public function available(): bool
    {
        return Module::find('Store')?->isEnabled() ?? false;
    }

    /** @return Builder<StoreProduct> */
    private function query(): Builder
    {
        return StoreProduct::query()->where('is_active', true)->where('is_archived', false)
            ->where(fn (Builder $query) => $query->whereNull('store_category_id')->orWhereHas('category', fn (Builder $category) => $category->where('is_active', true)))
            ->with(['category', 'imageMedia', 'images', 'variants' => fn ($variants) => $variants->where('is_active', true)->where('is_archived', false)->with('imageMedia')])
            ->orderBy('sort_order')->orderBy('id');
    }

    /** @return LengthAwarePaginator<int, StoreProduct> */
    public function products(?int $categoryId = null, ?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        if (! $this->available()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, max(1, min(100, $perPage)));
        }
        $query = $this->query();
        if ($categoryId !== null) {
            $query->where('store_category_id', $categoryId);
        }
        if (filled($search)) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query->paginate(max(1, min(100, $perPage)))->withQueryString();
    }

    /** @return Collection<int, StoreCategory> */
    public function categories(): Collection
    {
        return $this->available() ? StoreCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get() : new Collection;
    }

    public function findBySlug(string $slug): ?StoreProduct
    {
        return $this->available() ? $this->query()->where('slug', $slug)->first() : null;
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
