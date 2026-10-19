<x-admin.layout title="Fotoğraf ve Video Galerisi">
    <div class="card mb-3"><div class="card-header"><h2 class="card-title me-auto">Fotoğraf ve Video Galerisi</h2>
        @can('galeriler.create')<a href="{{ route('admin.galeriler.create') }}" class="btn btn-primary">Galeri kaydı ekle</a>@endcan
    </div><div class="card-body">
        <nav class="nav nav-pills gap-2 mb-3" aria-label="Galeriler kategorileri">
            <a class="nav-link {{ empty($filters['category_id']) ? 'active' : '' }}" href="{{ route('admin.galeriler.index', array_filter(['search' => $filters['search'] ?? null, 'type' => $filters['type'] ?? null])) }}">Tümü</a>
            @foreach ($categories as $category)
                <a class="nav-link {{ (int)($filters['category_id'] ?? 0) === $category->id ? 'active' : '' }}" href="{{ route('admin.galeriler.index', array_filter(['category_id' => $category->id, 'search' => $filters['search'] ?? null, 'type' => $filters['type'] ?? null])) }}">{{ $category->name }}</a>
            @endforeach
        </nav>
        <form method="GET" class="d-flex gap-2 flex-wrap">
            @if (!empty($filters['category_id']))<input type="hidden" name="category_id" value="{{ $filters['category_id'] }}">@endif
            <input class="form-control w-auto" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Başlık veya açıklama ara" aria-label="Ara" maxlength="255">
            <select name="type" class="form-select w-auto" aria-label="Tür filtresi">
                <option value="">Tüm türler</option><option value="photo" @selected(($filters['type'] ?? '') === 'photo')>Fotoğraf</option><option value="video" @selected(($filters['type'] ?? '') === 'video')>Video</option>
            </select>
            <button class="btn">Filtrele</button>
        </form>
        @if ($errors->any())<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>@endif
        @if ($canReorder)<p class="text-secondary mt-3 mb-0">Kartları sürükleyerek sıralayın. Sıra otomatik kaydedilir.</p>@endif
    </div></div>
    @if(auth('admin')->user()->canAny(['galeriler.update', 'galeriler.delete']))
        <form id="galeriler-bulk" method="POST" action="{{ route('admin.galeriler.bulk') }}" data-confirm="Seçili kayıtlar için toplu işlemi uygulamak istediğinizden emin misiniz?" class="d-flex gap-2 mb-3 flex-wrap">
            @csrf
            <select name="action" class="form-select w-auto" aria-label="Toplu işlem">
                @can('galeriler.update')<option value="move">Kategori değiştir</option>@endcan
                @can('galeriler.delete')<option value="delete">Sil</option>@endcan
            </select>
            <select name="galeriler_category_id" class="form-select w-auto" aria-label="Hedef kategori"><option value="">Hedef kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>
            <button class="btn">Seçilenlere uygula</button>
            <label class="form-check align-self-center"><input type="checkbox" class="form-check-input" data-galeriler-select-all><span class="form-check-label">Bu sayfadakileri seç</span></label>
        </form>
    @endif
    <div class="row g-3" data-galeriler-grid @if($canReorder) data-galeriler-reorder="{{ route('admin.galeriler.reorder') }}" data-galeriler-category="{{ $filters['category_id'] }}" @endif>
        @forelse ($items as $item)
            <div class="col-sm-6 col-lg-4 col-xl-3" data-galeriler-card="{{ $item->id }}" @if($canReorder) draggable="true" @endif>
                <article class="card h-100">
                    <div class="ratio ratio-16x9 bg-secondary-lt overflow-hidden">
                        @if($item->type === 'photo' && $item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title ?? $item->category?->name ?? 'Galeri fotoğrafı' }}" class="object-fit-cover" loading="lazy" draggable="false">
                        @elseif($item->video && $item->video['thumbnail_url'])
                            <img src="{{ $item->video['thumbnail_url'] }}" alt="{{ $item->title ?? 'Video kapağı' }}" class="object-fit-cover" loading="lazy" draggable="false">
                        @elseif($item->video)
                            <iframe src="{{ $item->video['embed_url'] }}" title="{{ $item->title ?? 'Video önizlemesi' }}" loading="lazy" allowfullscreen></iframe>
                        @else <span class="d-flex align-items-center justify-content-center">Önizleme yok</span> @endif
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 align-items-center mb-2">
                            @if(auth('admin')->user()->canAny(['galeriler.update', 'galeriler.delete']))<input type="checkbox" class="form-check-input" name="ids[]" value="{{ $item->id }}" form="galeriler-bulk" aria-label="Kaydı seç">@endif
                            <span class="badge bg-blue-lt">{{ $item->type === 'photo' ? 'Fotoğraf' : 'Video' }}</span>
                        </div>
                        @if($item->title)<h3 class="card-title">{{ $item->title }}</h3>@endif
                        <p class="text-secondary">{{ $item->category?->name ?? 'Kategorisiz' }}</p>
                        <div class="d-flex gap-2">
                            @can('galeriler.update')<a href="{{ route('admin.galeriler.edit', $item) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>@endcan
                            @can('galeriler.delete')<form method="POST" action="{{ route('admin.galeriler.destroy', $item) }}" data-confirm="Galeri kaydını silmek istediğinizden emin misiniz?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Sil</button></form>@endcan
                        </div>
                    </div>
                </article>
            </div>
        @empty <div class="col-12"><div class="card card-body text-secondary">Bu filtrelerle eşleşen kayıt bulunmuyor.</div></div> @endforelse
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
</x-admin.layout>
