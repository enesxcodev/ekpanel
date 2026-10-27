@if($products->isNotEmpty())
    <section id="urunler" class="section-space bg-slate-50" data-product-filter>
        <div class="site-container">
            <div class="section-heading">
                <div><p class="eyebrow">{{ $copy['products_eyebrow'] }}</p><h2 class="mt-2">{{ $copy['products_title'] }}</h2></div>
                @if($copy['products_action_url'] && $copy['products_action_label'])<a href="{{ $copy['products_action_url'] }}" class="text-link">{{ $copy['products_action_label'] }} <span aria-hidden="true">→</span></a>@endif
            </div>
            <div class="mt-6 flex flex-wrap gap-2" role="group" aria-label="Ürün kategorileri">
                <button type="button" class="filter-button" data-category="all" aria-pressed="true">{{ $copy['products_all_label'] }}</button>
                @foreach($categories as $category)
                    <button type="button" class="filter-button" data-category="{{ $category['id'] }}" aria-pressed="false">{{ $category['name'] }}</button>
                @endforeach
            </div>
            <div class="mt-5 grid items-start gap-5 md:grid-cols-3">
                @foreach($products as $product)
                    <article class="content-card" data-product-category="{{ $product['category_id'] }}">
                        <div class="flex items-center gap-4">
                            @if($product['image'])
                                <img src="{{ $product['image'] }}" alt="" width="48" height="48" class="size-12 rounded-xl object-cover" loading="lazy">
                            @else
                                <span class="icon-tile"><x-frontend.default.icon :name="$product['icon']" /></span>
                            @endif
                            <span class="badge">{{ $product['category'] }}</span>
                        </div>
                        <h3 class="mt-4 text-lg font-extrabold tracking-tight">{{ $product['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $product['description'] }}</p>
                        @if($copy['products_action_url'] && $copy['products_action_label'])<a href="{{ $copy['products_action_url'] }}" class="text-link mt-5">{{ $copy['products_item_action_label'] }} <span aria-hidden="true">→</span></a>@endif
                    </article>
                @endforeach
            </div>
            <p class="sr-only" aria-live="polite" data-filter-status></p>
        </div>
    </section>
@endif
