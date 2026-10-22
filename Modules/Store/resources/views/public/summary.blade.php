<div class="my-4 rounded border p-4">@foreach($quote['lines'] as $line)<p>{{ $line['product']->title }} {{ $line['variant']?->title }} × {{ $line['quantity'] }}: {{ \Modules\Store\Services\StoreMoney::format($line['amounts']['gross'],$quote['currency']) }}</p>@endforeach
<p>Ürünler (vergisiz): {{ \Modules\Store\Services\StoreMoney::format($quote['net'],$quote['currency']) }}</p>
<p>Vergi: {{ \Modules\Store\Services\StoreMoney::format($quote['tax']+$quote['shippingAmounts']['tax'],$quote['currency']) }}</p>
<p>Teslimat (vergi dahil): {{ \Modules\Store\Services\StoreMoney::format($quote['shippingAmounts']['gross'],$quote['currency']) }}</p>
<p>Genel toplam: {{ \Modules\Store\Services\StoreMoney::format($quote['total'],$quote['currency']) }}</p></div>
