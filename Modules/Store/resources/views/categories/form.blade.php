<x-admin.layout :title="$category ? 'Kategori düzenle' : 'Kategori ekle'"><div class="card"><div class="card-body"><form method="POST" action="{{ $category ? route('admin.store.categories.update',$category) : route('admin.store.categories.store') }}">@csrf @if($category) @method('PUT') @endif
<x-admin.form.input name="name" label="Ad" :value="$category?->name" data-slug-title required />
<x-admin.form.input name="slug" label="Kısa bağlantı" :value="$category?->slug" data-slug-field required />
<x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$category?->sort_order ?? 0" min="0" required />
<x-admin.form.select name="is_active" label="Durum" :options="[['value'=>1,'label'=>'Aktif'],['value'=>0,'label'=>'Pasif']]" :value="(int)($category?->is_active ?? true)" :placeholder="false" />
<button class="btn btn-primary">Kaydet</button><a class="btn btn-link" href="{{ route('admin.store.categories.index') }}">Vazgeç</a></form></div></div></x-admin.layout>
