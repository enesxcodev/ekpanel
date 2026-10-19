@if($user->profile_image)
    <img src="{{ Storage::disk('public')->url($user->profile_image) }}" alt="{{ $user->name }} {{ $user->surname }}" class="avatar" loading="lazy">
@else
    <span class="text-muted">—</span>
@endif
