@if($next !== null)
    <a href="{{ route('admin.konum.index', $parameters) }}">{{ $location->name }}</a>
@else
    {{ $location->name }}
@endif
