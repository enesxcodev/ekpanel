@if ($document->file)
    <a href="{{ Storage::disk('public')->url($document->file) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Dosyayı aç</a>
@else
    <span class="text-secondary">—</span>
@endif
