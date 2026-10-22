<x-admin.layout title="Log İzleme">
    <style>
        .log-viewer-layout { display:grid; grid-template-columns:minmax(230px, 285px) minmax(0, 1fr); gap:1rem; align-items:stretch; }
        .log-viewer-tree { max-height:65vh; overflow:auto; padding:.75rem; }
        .log-tree-group { margin:.1rem 0; }
        .log-tree-group summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:.5rem; padding:.45rem .5rem; border-radius:.35rem; color:var(--tblr-secondary-color); font-size:.84rem; }
        .log-tree-group summary::-webkit-details-marker { display:none; }
        .log-tree-group summary:hover,.log-tree-file:hover { background:var(--tblr-bg-surface-secondary); }
        .log-tree-children { padding-left:.8rem; }
        .log-tree-file { display:flex; align-items:center; gap:.5rem; padding:.45rem .55rem; border-radius:.35rem; color:inherit; text-decoration:none; font-size:.82rem; overflow-wrap:anywhere; }
        .log-tree-file.active { color:var(--tblr-primary); background:rgba(var(--tblr-primary-rgb),.08); font-weight:600; }
        .log-viewer-output { max-height:65vh; overflow:auto; background:var(--tblr-bg-surface-secondary); border:1px solid var(--tblr-border-color); border-radius:.35rem; }
        .log-viewer-line { display:grid; grid-template-columns:3.25rem minmax(0,1fr); font: .76rem/1.6 var(--tblr-font-monospace,monospace); }
        .log-viewer-line-number { color:var(--tblr-secondary-color); text-align:right; padding:.15rem .65rem; border-right:1px solid var(--tblr-border-color); user-select:none; }
        .log-viewer-line-content { padding:.15rem .75rem; white-space:pre-wrap; overflow-wrap:anywhere; }
        .log-viewer-meta { min-width:0; overflow-wrap:anywhere; }
        @media(max-width:767.98px) { .log-viewer-layout { grid-template-columns:minmax(0,1fr); } .log-viewer-tree,.log-viewer-output { max-height:55vh; } }
    </style>

    <div class="alert alert-warning" role="note">
        <div class="d-flex gap-2 align-items-start">
            <span class="text-warning fw-bold" aria-hidden="true">!</span>
            <div>Log kayıtları hassas bilgiler içerebilir. Bu ekran yalnızca süper yöneticilere açıktır.</div>
        </div>
    </div>

    @if ($readError)
        <div class="alert alert-danger" role="alert">Seçili log dosyası artık bulunamıyor veya okunamıyor. Dosya listesini yenileyip tekrar seçin.</div>
    @endif

    <div class="log-viewer-layout">
        <section class="card" aria-labelledby="log-files-heading">
            <div class="card-header">
                <h2 class="card-title" id="log-files-heading">Log dosyaları</h2>
                <span class="text-secondary small">storage/logs</span>
            </div>
            <nav class="log-viewer-tree" aria-label="Log dosyası ağacı">
                @if (! $logsAvailable)
                    <div class="empty">
                        <p class="empty-title">Log klasörü kullanılamıyor</p>
                        <p class="empty-subtitle text-secondary">Uygulamanın storage/logs klasörüne erişilemedi.</p>
                    </div>
                @elseif ($tree === [])
                    <div class="empty">
                        <p class="empty-title">Log dosyası bulunamadı</p>
                        <p class="empty-subtitle text-secondary">storage/logs altında görüntülenebilir .log dosyası yok.</p>
                    </div>
                @else
                    @include('logviewer::partials.tree', ['nodes' => $tree, 'selectedPath' => $selectedPath, 'lineCount' => $lineCount])
                @endif
            </nav>
        </section>

        <section class="card" aria-labelledby="log-content-heading">
            @if ($selectedLog !== null)
                <div class="card-header d-flex flex-wrap align-items-center gap-3">
                    <div class="log-viewer-meta me-auto">
                        <h2 class="card-title mb-1" id="log-content-heading">{{ $selectedLog['name'] }}</h2>
                        <div class="text-secondary small">storage/logs/{{ $selectedLog['path'] }} · {{ number_format($selectedLog['size'] / 1024, 1, ',', '.') }} KB · Son değişiklik {{ date('d.m.Y H:i:s', $selectedLog['modified_at']) }}</div>
                    </div>
                    <form method="GET" action="{{ route('admin.logviewer.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                        <input type="hidden" name="file" value="{{ $selectedLog['path'] }}">
                        <label for="log-line-count" class="form-label mb-0 small text-secondary">Satır</label>
                        <select id="log-line-count" name="lines" class="form-select form-select-sm w-auto">
                            @foreach ([100, 200, 500] as $option)
                                <option value="{{ $option }}" @selected($lineCount === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <x-admin.icon name="refresh" class="icon icon-sm me-1" />Yenile
                        </button>
                    </form>
                </div>
                <div class="card-body pt-0">
                    @if ($selectedLog['truncated'])
                        <div class="text-secondary small mb-2">Dosyanın en fazla 1 MiB'lık son bölümü okundu.</div>
                    @endif
                    <div class="log-viewer-output" role="region" aria-label="Log satırları">
                        @forelse ($selectedLog['lines'] as $index => $line)
                            <div class="log-viewer-line">
                                <span class="log-viewer-line-number" aria-hidden="true">{{ $index + 1 }}</span>
                                <span class="log-viewer-line-content">{{ $line }}</span>
                            </div>
                        @empty
                            <div class="empty py-5"><p class="empty-title">Bu dosya boş</p></div>
                        @endforelse
                    </div>
                    <div class="d-flex justify-content-between gap-2 text-secondary small mt-2">
                        <span>Son {{ count($selectedLog['lines']) }} satır gösteriliyor</span>
                        <span>Son çekim {{ $lastReadAt }}</span>
                    </div>
                </div>
            @else
                <div class="card-body">
                    <div class="empty py-5">
                        <div>
                            <x-admin.icon name="file-text" class="icon icon-lg text-secondary mb-2" />
                            <p class="empty-title">Görüntülemek için bir log dosyası seçin</p>
                            <p class="empty-subtitle text-secondary">Dosya içeriği yalnız seçim yaptıktan sonra okunur.</p>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-admin.layout>
