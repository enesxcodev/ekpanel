<x-admin.layout :title="'Sipariş '.$order->number">
<div class="card"><div class="card-body"><p>{{ $order->customer_name }} / {{ $order->customer_email }} / {{ $order->customer_phone }}</p>
<p>Teslimat: {{ $order->delivery_snapshot['method'] ?? 'shipping' }}</p>
@foreach(['shipping_address'=>'Teslimat adresi','billing_address'=>'Fatura adresi'] as $field=>$label)<p>{{ $label }}: {{ implode(', ',array_filter($order->{$field} ?? [])) }}</p>@endforeach
@foreach($order->lines as $line)<p>{{ $line->product_title }} / {{ $line->variant_title }} / {{ $line->sku }} × {{ $line->quantity }}: {{ \Modules\Store\Services\StoreMoney::format($line->line_total_minor,$order->currency) }}</p>@endforeach
<p>Toplam: {{ \Modules\Store\Services\StoreMoney::format($order->total_minor,$order->currency) }}</p>
@if($order->status==='cancelled' && $order->payment_status==='paid')<div class="alert alert-warning">Ödeme iadesi gerekiyor. İptal işlemi para iadesi gerçekleştirmez.</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
@can('store.orders.update')<form method="POST" action="{{ route('admin.store.orders.update',$order) }}">@csrf @method('PUT')
<x-admin.form.select name="status" label="Sipariş durumu" :options="[['value'=>'pending','label'=>'Bekliyor'],['value'=>'confirmed','label'=>'Onaylandı'],['value'=>'preparing','label'=>'Hazırlanıyor'],['value'=>'ready','label'=>'Teslimata hazır'],['value'=>'shipped','label'=>'Gönderildi'],['value'=>'completed','label'=>'Tamamlandı'],['value'=>'cancelled','label'=>'İptal']]" :value="$order->status" :placeholder="false" />
@can('store.orders.payment')<x-admin.form.select name="payment_status" label="Manuel ödeme kaydı" :options="[['value'=>'unpaid','label'=>'Bekliyor'],['value'=>'paid','label'=>'Manuel ödendi'],['value'=>'refunded','label'=>'Manuel iade kaydı']]" :value="$order->payment_status" :placeholder="false" />@else<input type="hidden" name="payment_status" value="{{ $order->payment_status }}">@endcan
<x-admin.form.input name="tracking_number" label="Manuel takip numarası" :value="$order->tracking_number" />
<x-admin.form.textarea name="note" label="Referans ve açıklama (ödeme değişiminde zorunlu)" />
<button class="btn btn-primary">Güncelle</button></form>@endcan
<h2 class="mt-4">Geçmiş</h2>@foreach($order->status_history ?? [] as $entry)<p>{{ $entry['at'] }} / {{ $entry['status'] }} / {{ $entry['payment_status'] ?? '' }} / {{ $entry['note'] ?? '' }}</p>@endforeach
</div></div></x-admin.layout>
