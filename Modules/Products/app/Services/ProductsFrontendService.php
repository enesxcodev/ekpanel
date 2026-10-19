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
    public function available(): bool
    {
        return Module::find('Products')?->isEnabled() ?? false;
    }

    /** @return Collection<int, Product> */
    public function products(?int $categoryId = null, ?int $limit = null): Collection
    {
        if (! $this->available()) {
            return new Collection;
        }
        $query = $this->visible()->with(['category', 'imageMedia', 'images'])->orderBy('sort_order')->orderBy('id');
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
    public function categories(): Collection
    {
        return $this->available() ? ProductCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get() : new Collection;
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->available() ? $this->visible()->with(['category', 'imageMedia', 'images'])->where('slug', $slug)->first() : null;
    }

    /** @return array{key: string, context: string}|null */
    public function formFor(Product $product): ?array
    {
        if (! $this->available()) {
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
        if (! $this->available() || ! $form->is_active) {
            return null;
        }
        $product = $this->visible()->where('site_form_id', $form->id)->lockForUpdate()->find($id);

        return $product ? ['type' => 'product', 'id' => $product->id, 'title' => $product->title, 'slug' => $product->slug] : null;
    }

    /** @return Builder<Product> */
    private function visible(): Builder
    {
        return Product::query()->where('is_active', true)->where(function (Builder $query): void {
            $query->whereNull('product_category_id')->orWhereHas('category', fn (Builder $category): Builder => $category->where('is_active', true));
        });
    }
}
