@if ($testimonial->image_url)
    <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->customer_name }}" class="avatar avatar-sm object-fit-cover" width="32" height="32" loading="lazy">
@else
    <span class="text-secondary">—</span>
@endif
