<x-admin.layout title="Müşteri yorumları">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Müşteri yorumları ve başarı hikâyeleri</h2>
            <div class="btn-group">@foreach($activeLanguages as $language)<a class="btn btn-sm {{ $selectedLanguage?->id === $language->id ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('admin.testimonials.index', ['lang' => $language->code]) }}">{{ strtoupper($language->code) }}</a>@endforeach</div>
            @can('testimonials.create')
                <a href="{{ route('admin.testimonials.create', ['lang' => 'tr']) }}" class="btn btn-primary">Yorum ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="testimonials-table" :source="route('admin.testimonials.data', ['lang' => $selectedLanguage?->code])"
            :options="['order' => [[7, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Görsel', 'data' => 'photo', 'name' => 'photo', 'orderable' => false, 'searchable' => false],
                ['title' => 'Müşteri adı', 'data' => 'customer_name', 'name' => 'customer_name', 'orderable' => false, 'searchable' => false],
                ['title' => 'Firma', 'data' => 'company', 'name' => 'company', 'orderable' => false, 'searchable' => false],
                ['title' => 'Unvan', 'data' => 'job_title', 'name' => 'job_title', 'orderable' => false, 'searchable' => false],
                ['title' => 'Yorum', 'data' => 'content', 'name' => 'content', 'orderable' => false, 'searchable' => false],
                ['title' => 'Yayın durumu', 'data' => 'is_active', 'name' => 'is_active', 'searchable' => false],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
