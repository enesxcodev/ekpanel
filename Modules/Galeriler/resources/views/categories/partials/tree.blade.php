<ul class="list-unstyled {{ $parentId ? 'ms-4 border-start ps-3' : '' }}">
@foreach ($byParent->get($parentId ?? 0, collect()) as $category)
    <li class="mb-3">
        <div class="d-flex gap-3 align-items-center flex-wrap">
            @if ($category->icon)<x-admin.icon :name="$category->icon" />@endif
            <div class="me-auto"><strong>{{ $category->name }}</strong>@if ($category->short_description)<div class="text-secondary small">{{ $category->short_description }}</div>@endif</div>
            @include('galeriler::categories.partials.actions')
        </div>
        @if ($byParent->has($category->id))@include('galeriler::categories.partials.tree', ['parentId' => $category->id])@endif
    </li>
@endforeach
</ul>