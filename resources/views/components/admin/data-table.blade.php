@props([
    'id',
    'source',
    'columns',
    'options' => [],
])

@php
    $configId = $id.'-config';
    $config = array_merge($options, ['columns' => $columns]);
@endphp

<div class="table-responsive">
    <table
        id="{{ $id }}"
        data-source="{{ $source }}"
        data-config="{{ $configId }}"
        {{ $attributes->class(['table', 'table-vcenter', 'card-table', 'table-hover', 'js-data-table']) }}
    >
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column['title'] }}</th>
                @endforeach
            </tr>
        </thead>
    </table>
</div>

@push('scripts')
    <script type="application/json" id="{{ $configId }}">{!! \Illuminate\Support\Js::encode($config) !!}</script>
@endpush
