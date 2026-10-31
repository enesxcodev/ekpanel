<div class="my-4 rounded border p-4">@foreach($quote['lines'] as $line)<p>{{ $line['product']->title }} {{ $line['variant']?->title }} × {{ $line['quantity'] }}: {{ \Modules\Store\Services\StoreMoney::format($line['amounts']['gross'],$quote['currency']) }}</p>@endforeach
<p>{{ __('store::store.items_excluding_tax') }}: {{ \Modules\Store\Services\StoreMoney::format($quote['net'],$quote['currency']) }}</p>
<p>{{ __('store::store.tax') }}: {{ \Modules\Store\Services\StoreMoney::format($quote['tax']+$quote['shippingAmounts']['tax'],$quote['currency']) }}</p>
<p>{{ __('store::store.delivery_including_tax') }}: {{ \Modules\Store\Services\StoreMoney::format($quote['shippingAmounts']['gross'],$quote['currency']) }}</p>
<p>{{ __('store::store.grand_total') }}: {{ \Modules\Store\Services\StoreMoney::format($quote['total'],$quote['currency']) }}</p></div>
