@if ($sector->image)
    <img src="{{ Storage::disk('public')->url($sector->image) }}" alt="{{ $sector->title }}" class="avatar avatar-sm object-fit-cover" width="32" height="32" loading="lazy">
@else
    <span class="avatar avatar-sm">{{ mb_substr($sector->title, 0, 1) }}</span>
@endif
