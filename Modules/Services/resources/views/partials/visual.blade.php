@if ($service->image)
    <img src="{{ Storage::disk('public')->url($service->image) }}" alt="{{ $service->title }}" class="avatar" loading="lazy">
@elseif ($service->icon)
    <x-admin.icon :name="$service->icon" />
@else
    <span class="text-secondary">—</span>
@endif
