@if ($member->image)
    <img src="{{ Storage::disk('public')->url($member->image) }}" alt="{{ $member->name }}" class="avatar avatar-sm object-fit-cover" width="32" height="32" loading="lazy">
@else
    <span class="avatar avatar-sm">{{ mb_substr($member->name, 0, 1) }}</span>
@endif
