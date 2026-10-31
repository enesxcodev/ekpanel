@csrf
@isset($product) @method('PUT') @endisset
<input type="hidden" name="lang_id" value="{{ $language?->id }}">
@if($errors->any())
    <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@php
    $errorKey = array_key_first($errors->messages()) ?? '';
    $errorTab = match (true) {
        str_starts_with($errorKey, 'variants'), str_starts_with($errorKey, 'option_groups'), $errorKey === 'has_variants' => 'variants',
        in_array($errorKey, ['sku', 'price', 'compare_at_price', 'stock_quantity', 'stock_reason', 'tracks_stock', 'low_stock_threshold', 'allow_backorders']) => 'pricing',
        in_array($errorKey, ['weight_grams', 'width_cm', 'length_cm', 'height_cm', 'preparation_days', 'delivery_description']) => 'delivery',
        str_starts_with($errorKey, 'meta_'), str_starts_with($errorKey, 'share_image') => 'seo',
        default => $errorKey !== '' ? 'information' : '',
    };
@endphp
<div data-store-form data-form-key="{{ $product?->id ?? 'new' }}" data-error-tab="{{ $errorTab }}">
@if($canImport)
    <div class="card mb-3"><div class="card-body" data-store-catalog data-url="{{ route('admin.store.catalog') }}">
        <label class="form-label" for="catalog-search">Katalogdan ürün aktar</label>
        <div class="d-flex gap-2"><input id="catalog-search" class="form-control" placeholder="Ürün adı veya katalog ID"><button type="button" class="btn btn-outline-primary" data-search>Ara</button></div>
        <div data-results class="mt-3" aria-live="polite"></div><button type="button" class="btn btn-link" data-next hidden>Sonraki sayfa</button>
        <p class="text-secondary mt-2">Kapak ve galeri dahil içerik tek seferlik kopyalanır. Fiyat, SKU, stok ve varyantları burada belirleyin.</p>
    </div></div>
@endif
<input type="hidden" name="source_product_id" value="{{ old('source_product_id', $product?->source_product_id) }}">
<div class="row g-3">
<div class="col-lg-8"><div class="card"><div class="card-body">
    <ul class="nav nav-tabs flex-wrap mb-3" role="tablist">
        @foreach(['information' => 'Ürün bilgileri', 'pricing' => 'Fiyat ve stok', 'variants' => 'Varyantlar', 'delivery' => 'Teslimat', 'seo' => 'SEO ayarları'] as $key => $label)
        <li class="nav-item" role="presentation"><button class="nav-link {{ $loop->first ? 'active' : '' }}" id="store-tab-{{ $key }}" data-bs-toggle="tab" data-bs-target="#store-{{ $key }}" type="button" role="tab" aria-controls="store-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}</button></li>
        @endforeach
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="store-information" role="tabpanel" aria-labelledby="store-tab-information">
            <x-admin.form.input name="title" label="Ürün adı" :value="$product?->title" data-slug-title required maxlength="255" />
            <x-admin.form.input name="slug" label="Kısa bağlantı" :value="$product?->slug" data-slug-field maxlength="255" />
            <x-admin.form.select name="store_category_id" label="Kategori" :options="$categories" :value="$product?->store_category_id" placeholder="Kategorisiz" />
            <x-admin.form.textarea name="short_description" label="Kısa açıklama" :value="$product?->short_description" rows="3" maxlength="1000" />
            <x-admin.form.textarea name="content" label="Açıklama" :value="$product?->content" data-hugerte rows="10" maxlength="65535" />
            <x-admin.media.field name="image_media_id" label="Kapak görseli" :config="$mediaConfig" :value="$product?->image_media_id" :preview-url="$product?->image_url" remove-name="remove_image" />
            <div data-store-gallery>
                <x-admin.media.picker name="image_media_ids" label="Galeri" :config="$mediaConfig" kind="image" :multiple="true" :extensions="['jpg','jpeg','png','webp']" :value="old('image_media_ids', $product?->images->modelKeys() ?? [])" :disabled="!auth('admin')->user()->can('media.view')" />
                <ol class="list-group my-3" data-gallery-order aria-label="Galeri sırası">@foreach($product?->images ?? [] as $image)<li data-id="{{ $image->id }}" data-name="{{ $image->name }}">{{ $image->name }}</li>@endforeach</ol>
            </div>
            <x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
        </div>
        <div class="tab-pane fade" id="store-pricing" role="tabpanel" aria-labelledby="store-tab-pricing">
            <x-admin.form.input name="sku" label="Ürün SKU" :value="$product?->sku" required maxlength="100" />
            <x-admin.form.input name="price" label="Satış fiyatı (TL)" :value="$product ? \Modules\Store\Services\StoreMoney::decimal($product->price_minor) : '0.00'" inputmode="decimal" required />
            <x-admin.form.input name="compare_at_price" label="Karşılaştırma fiyatı (TL)" :value="$product?->compare_at_price_minor !== null ? \Modules\Store\Services\StoreMoney::decimal($product->compare_at_price_minor) : null" inputmode="decimal" />
            <p class="text-secondary">Verginin dahil/hariç olması Mağaza ayarlarından belirlenir. Varyantlı ürünlerde bu fiyat temel fiyattır; stok varyanttan düşülür.</p>
            <x-admin.form.select name="tracks_stock" label="Stok takibi" :options="[['value'=>1,'label'=>'Açık'],['value'=>0,'label'=>'Kapalı']]" :value="(int)($product?->tracks_stock ?? true)" :placeholder="false" />
            <x-admin.form.input name="stock_quantity" label="Basit ürün stok adedi" type="number" :value="$product?->stock_quantity ?? 0" required />
            <x-admin.form.input name="low_stock_threshold" label="Düşük stok eşiği" type="number" :value="$product?->low_stock_threshold ?? 0" min="0" required />
            <x-admin.form.select name="allow_backorders" label="Stok yokken satış" :options="[['value'=>0,'label'=>'Kapalı'],['value'=>1,'label'=>'Açık']]" :value="(int)($product?->allow_backorders ?? false)" :placeholder="false" />
            <x-admin.form.input name="stock_reason" label="Stok değişikliği gerekçesi" maxlength="255" />
        </div>
        <div class="tab-pane fade" id="store-variants" role="tabpanel" aria-labelledby="store-tab-variants" data-variant-editor>
            <x-admin.form.select name="has_variants" label="Varyant kullan" :options="[['value'=>0,'label'=>'Hayır'],['value'=>1,'label'=>'Evet']]" :value="(int)($product?->has_variants ?? false)" :placeholder="false" />
            <p>En fazla 3 seçenek grubu tanımlayın; değerleri virgülle ayırın. Örnek: Renk / Siyah, Beyaz. Görselleri galeriden seçebilirsiniz.</p>
            <div data-option-groups></div>
            <button type="button" class="btn btn-outline-secondary mb-3" data-add-group>Seçenek grubu ekle</button>
            <button type="button" class="btn btn-outline-primary mb-3" data-generate>Kombinasyonları oluştur / güncelle</button>
            <p class="text-secondary">Aynı kombinasyonun mevcut ID, fiyat ve stoğu korunur. Kaldırılan satırlar arşivlenir. Varyant modunu değiştirince stok otomatik taşınmaz.</p>
            <div data-variant-rows></div>
            <script type="application/json" data-groups-json>@json(old('option_groups', $product?->option_groups ?? []))</script>
            <script type="application/json" data-variants-json>@json(old('variants', $variants))</script>
        </div>
        <div class="tab-pane fade" id="store-delivery" role="tabpanel" aria-labelledby="store-tab-delivery">
            <p>Bu sürüm fiziksel ürün teslimatını destekler. Ücret ve teslim alma seçenekleri Mağaza ayarlarındadır.</p>
            @foreach(['weight_grams'=>'Ağırlık (gram)','width_cm'=>'En (cm)','length_cm'=>'Boy (cm)','height_cm'=>'Yükseklik (cm)','preparation_days'=>'Hazırlık süresi (gün)'] as $field=>$label)
                <x-admin.form.input :name="$field" :label="$label" type="number" :value="$product?->{$field} ?? ($field === 'preparation_days' ? 0 : null)" min="0" />
            @endforeach
            <x-admin.form.textarea name="delivery_description" label="Teslimat açıklaması" :value="$product?->delivery_description" maxlength="2000" />
        </div>
        <div class="tab-pane fade" id="store-seo" role="tabpanel" aria-labelledby="store-tab-seo">
            <x-admin.form.input name="meta_title" label="Meta başlık" :value="$product?->meta_title" maxlength="255" />
            <x-admin.form.textarea name="meta_description" label="Meta açıklama" :value="$product?->meta_description" maxlength="500" />
            <x-admin.media.field name="share_image_media_id" label="Paylaşım görseli" :config="$mediaConfig" :value="$product?->share_image_media_id" remove-name="remove_share_image" />
            <p class="text-secondary">Boş alanlar ürün adı, kısa açıklama ve kapak görselinden oluşturulur.</p>
        </div>
    </div>
</div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body">
    <label class="form-label" for="store-language">{{ __('store::store.content_language') }}</label>
    <select id="store-language" class="form-select mb-3" onchange="if(this.value) location.href=this.value">
        @foreach($languages as $item)<option value="{{ $product ? route('admin.store.edit', ['product'=>$product, 'lang'=>$item['code']]) : route('admin.store.create', ['lang'=>$item['code']]) }}" @selected($language?->code === $item['code'])>{{ strtoupper($item['code']) }}{{ $product && !$item['has_translation'] ? ' — '.__('store::store.translation_missing') : '' }}</option>@endforeach
    </select>
    <x-admin.form.select name="is_active" label="Yayın durumu" :options="[['value'=>0,'label'=>'Taslak'],['value'=>1,'label'=>'Yayında']]" :value="(int)($product?->is_active ?? false)" :placeholder="false" />
    <x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$product?->sort_order ?? 0" min="0" required />
    @if($product?->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="img-fluid mb-3">@endif
    <button class="btn btn-primary w-100">{{ $product ? 'Değişiklikleri kaydet' : 'Ürünü kaydet' }}</button>
    <a href="{{ route('admin.store.index') }}" class="btn btn-link">Vazgeç</a>
</div></div></div></div></div>
