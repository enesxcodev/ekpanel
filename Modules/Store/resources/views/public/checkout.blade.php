@extends('store::public.layout')
@section('store-content')
<h1 class="mb-4 text-3xl">Siparişi tamamla</h1>
@if($pickup)<form method="GET" class="mb-4"><label>Teslimat yöntemi <select name="delivery_method" class="rounded border p-2"><option value="shipping" @selected($delivery==='shipping')>Kargo</option><option value="pickup" @selected($delivery==='pickup')>Mağazadan teslim al</option></select></label><button class="rounded border p-2">Özeti güncelle</button></form>@endif
<form method="POST" action="{{ route('store.checkout') }}" class="space-y-3">@csrf
<input type="hidden" name="checkout_key" value="{{ $key }}"><input type="hidden" name="quote" value="{{ $quote['fingerprint'] }}"><input type="hidden" name="delivery_method" value="{{ $delivery }}">
@foreach(['customer_name'=>'Ad soyad','customer_email'=>'E-posta','customer_phone'=>'Telefon'] as $field=>$label)<label class="block">{{ $label }}<input class="ml-2 rounded border p-2" name="{{ $field }}" value="{{ old($field,auth('web')->user()?->{['customer_name'=>'name','customer_email'=>'email','customer_phone'=>'phone'][$field]}) }}" required></label>@endforeach
@if($delivery==='shipping')
<fieldset class="space-y-2 rounded border p-4"><legend>Teslimat adresi</legend>
@foreach(['line1'=>'Adres','city'=>'Şehir','postal_code'=>'Posta kodu','country'=>'Ülke kodu (TR)'] as $field=>$label)<label class="block">{{ $label }}<input class="ml-2 rounded border p-2" name="address[{{ $field }}]" value="{{ old('address.'.$field,$field==='country'?'TR':'') }}" @required($field!=='postal_code')></label>@endforeach
</fieldset>
@endif
<details><summary>Farklı fatura adresi (isteğe bağlı)</summary>@foreach(['line1'=>'Adres','city'=>'Şehir','postal_code'=>'Posta kodu','country'=>'Ülke kodu'] as $field=>$label)<label class="block">{{ $label }}<input class="rounded border p-2" name="billing_address[{{ $field }}]" value="{{ old('billing_address.'.$field) }}"></label>@endforeach</details>
@include('store::public.summary',['quote'=>$quote])
<p>{{ $instructions }}</p><p>Sipariş oluşturulunca ödeme bekliyor durumuna alınır. Bu işlem ödeme tahsil etmez.</p>
<button class="rounded bg-green-700 p-3 text-white">Özeti onayla ve siparişi oluştur</button>
</form>
@endsection
