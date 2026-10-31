<x-admin.layout title="İçerikler">
    <div class="card">
        <div class="card-body"><label class="form-label" for="blog-contents-table-language">Dil</label><select id="blog-contents-table-language" class="form-select" data-table-filter="lang" data-table-target="blog-contents-table">@foreach (app(\Modules\Blog\Services\BlogLanguageService::class)->active() as $language)<option value="{{ $language->code }}">{{ config('languages.catalog.'.$language->code.'.name', $language->code) }}</option>@endforeach</select></div>
        <div class="card-header d-flex gap-3 align-items-center"><h2 class="card-title me-auto">İçerikler</h2>@can('blog.create')<a href="{{ route('admin.blog.create') }}" class="btn btn-primary">İçerik ekle</a>@endcan</div>
        <div class="card-body border-bottom">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6 col-xxl-4"><x-admin.form.category-tree-select :tree="$categoryTree" name="filter_category_id" id="blog-filter-category" label="Kategori" allow-empty empty-label="Tüm kategoriler" :filter-target="'blog-contents-table'" /></div>
                <div class="col-12 col-md-6 col-xxl-2"><label class="form-label" for="blog-filter-tag">Etiket</label><select id="blog-filter-tag" class="form-select js-select2" data-table-filter="tag_id" data-table-target="blog-contents-table"><option value="">Tüm etiketler</option>@foreach($tags as $tag)<option value="{{ $tag->id }}">{{ $tag->name }}</option>@endforeach</select></div>
                <div class="col-12 col-md-6 col-xxl-2"><label class="form-label" for="blog-filter-status">Durum</label><select id="blog-filter-status" class="form-select" data-table-filter="status" data-table-target="blog-contents-table"><option value="">Tümü</option>@foreach($statuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                <div class="col-12 col-md-6 col-xxl-2"><label class="form-label" for="blog-filter-from">Yayın tarihi başlangıç</label><input id="blog-filter-from" type="date" class="form-control" data-table-filter="date_from" data-table-target="blog-contents-table"></div>
                <div class="col-12 col-md-6 col-xxl-2"><label class="form-label" for="blog-filter-to">Yayın tarihi bitiş</label><input id="blog-filter-to" type="date" class="form-control" data-table-filter="date_to" data-table-target="blog-contents-table"></div>
            </div>
        </div>
        <x-admin.data-table id="blog-contents-table" :source="route('admin.blog.data')" :options="['order' => [[0, 'desc']]]" :columns="[
            ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
            ['title' => 'Başlık', 'data' => 'title', 'name' => 'title'],
            ['title' => 'Kategori yolu', 'data' => 'category_path', 'name' => 'category_path', 'orderable' => false],
            ['title' => 'Yazar', 'data' => 'author_name', 'name' => 'author.name', 'orderable' => false],
            ['title' => 'Durum', 'data' => 'status', 'name' => 'status', 'searchable' => false],
            ['title' => 'Yayın tarihi', 'data' => 'published_at', 'name' => 'published_at'],
            ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
        ]" />
    </div>
</x-admin.layout>
