@csrf
@isset($product) @method('PUT') @endisset
<x-admin.form.input name="title" :value="$product->title ?? null" label="Ürün adı" data-slug-title required maxlength="255" />
<x-admin.form.input name="slug" :value="$product->slug ?? null" label="Kısa bağlantı" data-slug-field maxlength="255" />
<x-admin.form.select name="product_category_id" :value="$product->product_category_id ?? null" label="Kategori" :options="$categories" placeholder="Kategorisiz" />
<x-admin.form.textarea name="short_description" :value="$product->short_description ?? null" label="Kısa açıklama" rows="3" maxlength="1000" />
<x-admin.form.textarea name="content" :value="$product->content ?? null" label="Detay açıklaması" data-hugerte rows="10" maxlength="65535" />
<x-admin.media.field name="image_media_id" label="Kapak görseli" :config="$mediaConfig" :value="$product->image_media_id ?? null" :preview-url="$product?->image_url" remove-name="remove_image" />
<div data-product-gallery>
    <x-admin.media.picker name="image_media_ids" label="Galeri" :config="$mediaConfig" kind="image" :multiple="true" :extensions="['jpg', 'jpeg', 'png', 'webp']" :value="old('image_media_ids', $product?->images->modelKeys() ?? [])" :disabled="!auth('admin')->user()->can('media.view')" />
    <ol class="list-group my-3" data-product-gallery-order aria-label="Galeri sırası">
        @foreach($product?->images ?? [] as $image)<li data-media-id="{{ $image->id }}" data-media-name="{{ $image->name }}">{{ $image->name }}</li>@endforeach
    </ol>
</div>
@error('image_media_ids')<div class="text-danger">{{ $message }}</div>@enderror
@foreach($errors->get('image_media_ids.*') as $messages) @foreach($messages as $message)<div class="text-danger">{{ $message }}</div>@endforeach @endforeach
<x-admin.form.select name="site_form_id" label="İlgili form" :options="$forms" :value="$product->site_form_id ?? null" placeholder="Form gösterme" />
<x-admin.form.select name="is_active" label="Yayın durumu" :options="[['value' => '1', 'label' => 'Yayında'], ['value' => '0', 'label' => 'Taslak']]" :value="(int)($product->is_active ?? true)" :placeholder="false" required />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$product->sort_order ?? 0" min="0" max="65535" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary">{{ isset($product) ? 'Değişiklikleri kaydet' : 'Ürün ekle' }}</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-link">Vazgeç</a>
</div>
