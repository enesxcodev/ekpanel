@csrf
@isset($popup) @method('PUT') @endisset
<x-admin.form.input name="title" label="Başlık" :value="$popup->title ?? null" required maxlength="255" />
<x-admin.form.textarea name="content" label="İçerik" :value="$popup->content ?? null" required maxlength="100000" rows="8" />
<div class="mb-3">
    <x-admin.media.picker name="image_media_id" label="Görsel" :config="$mediaConfig" kind="image"
        :extensions="['jpg', 'jpeg', 'png', 'webp']" :value="old('image_media_id', $popup->image_media_id ?? null)"
        :preview-url="isset($popup) ? $popup->image_url : null" :disabled="!auth('admin')->user()->can('media.view')" />
    <div class="form-hint">İsteğe bağlı. JPG, PNG veya WebP; en fazla 4 MB. Seçimi değiştirmek dosyayı kütüphaneden silmez.</div>
    @error('image_media_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @cannot('media.view')<div class="form-hint">Görsel seçmek için medya görüntüleme izni gerekir.</div>@endcannot
</div>
<x-admin.form.input name="starts_at" label="Başlangıç" type="datetime-local" :value="isset($popup) ? $popup->starts_at?->format('Y-m-d\TH:i') : null" required />
<x-admin.form.input name="ends_at" label="Bitiş" type="datetime-local" :value="isset($popup) ? $popup->ends_at?->format('Y-m-d\TH:i') : null" required />
<div class="form-hint mb-3">Saat dilimi: {{ config('app.timezone') }}. Başlangıç dahildir; bitiş anında yayın sona erer.</div>
<x-admin.form.select name="repeat_policy" label="Tekrar gösterim politikası" :value="$popup->repeat_policy ?? 'never_after_close'" :placeholder="false" :options="[['value' => 'never_after_close', 'label' => 'Kapatıldıktan sonra tekrar gösterme'], ['value' => 'interval', 'label' => 'Belirlenen aralıkla tekrar göster']]" required />
<x-admin.form.input name="repeat_interval" label="Tekrar aralığı" type="number" :value="$popup->repeat_interval ?? null" min="1" max="36500" />
<x-admin.form.select name="repeat_unit" label="Aralık birimi" :value="$popup->repeat_unit ?? 'hours'" :placeholder="false" :options="[['value' => 'hours', 'label' => 'Saat'], ['value' => 'days', 'label' => 'Gün']]" />
<div class="form-hint mb-3">Aralıklı gösterimde sayı ve birim zorunludur. Süre, son gösterim veya kapatmanın daha yenisinden başlar; bir gün 24 saattir. Diğer politikada bu alanlar kullanılmaz.</div>
<x-admin.form.boolean name="is_active" label="Aktif" :value="$popup->is_active ?? true" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($popup) ? 'Değişiklikleri kaydet' : 'Popup ekle' }}</button>
    <a href="{{ route('admin.popup.index') }}" class="btn btn-link">Vazgeç</a>
</div>
