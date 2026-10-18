<x-admin.layout title="Müşteri yorumları">
    <div class="card">
        <div class="card-header d-flex gap-3 align-items-center">
            <h2 class="card-title me-auto">Müşteri yorumları ve başarı hikâyeleri</h2>
            @can('testimonials.create')
                <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">Yorum ekle</a>
            @endcan
        </div>
        <x-admin.data-table
            id="testimonials-table" :source="route('admin.testimonials.data')"
            :options="['order' => [[7, 'asc']]]"
            :columns="[
                ['title' => 'ID', 'data' => 'id', 'name' => 'id'],
                ['title' => 'Görsel', 'data' => 'photo', 'name' => 'photo', 'orderable' => false, 'searchable' => false],
                ['title' => 'Müşteri adı', 'data' => 'customer_name', 'name' => 'customer_name'],
                ['title' => 'Firma', 'data' => 'company', 'name' => 'company'],
                ['title' => 'Unvan', 'data' => 'job_title', 'name' => 'job_title'],
                ['title' => 'Yorum', 'data' => 'content', 'name' => 'content', 'orderable' => false],
                ['title' => 'Yayın durumu', 'data' => 'is_active', 'name' => 'is_active', 'searchable' => false],
                ['title' => 'Sıra', 'data' => 'sort_order', 'name' => 'sort_order'],
                ['title' => 'Oluşturulma tarihi', 'data' => 'created_at', 'name' => 'created_at'],
                ['title' => 'İşlemler', 'data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
            ]"
        />
    </div>
</x-admin.layout>
