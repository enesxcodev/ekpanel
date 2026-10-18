@php($moduleAssets = app(\App\Services\Admin\Modules\ModulePanelAssets::class)->forRoute(request()->route()?->getName()))
@foreach ($moduleAssets['css'] as $url)
    <link rel="stylesheet" href="{{ $url }}">
@endforeach
<script type="application/json" data-module-panel-assets>{!! json_encode($moduleAssets['js'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
