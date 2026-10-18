@csrf
@isset($tag) @method('PUT') @endisset
<x-admin.form.input name="name" label="Etiket adı" :value="$tag->name ?? null" required maxlength="255" data-slug-title />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$tag->slug ?? null" required maxlength="255" data-slug-field />
<div class="d-flex gap-2"><button type="submit" class="btn btn-primary">{{ isset($tag) ? 'Kaydet' : 'Etiket ekle' }}</button>@isset($tag)<a href="{{ route('admin.blog.tags.index') }}" class="btn btn-link">Vazgeç</a>@endisset</div>
