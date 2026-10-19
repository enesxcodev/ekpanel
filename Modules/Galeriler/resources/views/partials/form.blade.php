@csrf
@if ($item) @method('PUT') @endif
<x-admin.form.select name="galeriler_category_id" label="Kategori (isteğe bağlı)" :options="$categoryOptions" :value="$item->galeriler_category_id ?? null" placeholder="Kategorisiz" />
<x-admin.form.select name="type" label="Tür" :options="[['value' => 'photo', 'label' => 'Fotoğraf'], ['value' => 'video', 'label' => 'Video']]" :value="$item->type ?? 'photo'" :placeholder="false" required data-galeriler-type />
<div data-galeriler-photos @if(old('type', $item->type ?? 'photo') !== 'photo') hidden @endif>
    <x-admin.media.picker name="image_media_ids" label="Fotoğraflar" :config="$mediaConfig" kind="image" :multiple="true"
        :extensions="['jpg', 'jpeg', 'png', 'webp']" :value="old('image_media_ids', $item?->image_media_id ? [$item->image_media_id] : [])" :disabled="!auth('admin')->user()->can('media.view')" />
    <div class="form-hint">{{ $item ? 'Düzenlemede tek fotoğraf seçin.' : 'Seçilen her fotoğraf ayrı bir kayıt oluşturur. En fazla 100 fotoğraf seçilebilir.' }}</div>
    @error('image_media_ids')<div class="text-danger">{{ $message }}</div>@enderror
    @foreach ($errors->get('image_media_ids.*') as $messages) @foreach ($messages as $message)<div class="text-danger">{{ $message }}</div>@endforeach @endforeach
</div>
<div data-galeriler-video @if(old('type', $item->type ?? 'photo') !== 'video') hidden @endif>
    <x-admin.form.input name="video_url" label="Video bağlantısı" type="url" :value="$item->video_url ?? null" maxlength="2048" help="HTTPS YouTube veya Vimeo bağlantısı." />
</div>
<x-admin.form.input name="title" label="Başlık" :value="$item->title ?? null" maxlength="255" />
<x-admin.form.textarea name="description" label="Açıklama" :value="$item->description ?? null" rows="4" maxlength="10000" />
<x-admin.features.fields :form="$featureForm" :fields="$featureFields" :media-config="$mediaConfig" />
<div class="d-flex gap-2 mt-3"><button class="btn btn-primary">Kaydet</button><a href="{{ route('admin.galeriler.index') }}" class="btn btn-link">Vazgeç</a></div>
