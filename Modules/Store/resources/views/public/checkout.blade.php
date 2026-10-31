@extends('store::public.layout')
@section('store-content')
<h1 class="mb-4 text-3xl">{{ __('store::store.checkout_title') }}</h1>
@if($pickup)<form method="GET" action="{{ route('store.checkout.form',['lang'=>request()->route('lang') ?? request('lang','tr')]) }}" class="mb-4"><label>{{ __('store::store.delivery_method') }} <select name="delivery_method" class="rounded border p-2"><option value="shipping" @selected($delivery==='shipping')>{{ __('store::store.shipping') }}</option><option value="pickup" @selected($delivery==='pickup')>{{ __('store::store.pickup') }}</option></select></label><button class="rounded border p-2">{{ __('store::store.update_summary') }}</button></form>@endif
<form method="POST" action="{{ route('store.checkout',['lang'=>request()->route('lang') ?? request('lang','tr')]) }}" class="space-y-3">@csrf
<input type="hidden" name="checkout_key" value="{{ $key }}"><input type="hidden" name="quote" value="{{ $quote['fingerprint'] }}"><input type="hidden" name="delivery_method" value="{{ $delivery }}">
@foreach(['customer_name'=>'full_name','customer_email'=>'email','customer_phone'=>'phone'] as $field=>$label)<label class="block">{{ __('store::store.'.$label) }}<input class="ml-2 rounded border p-2" name="{{ $field }}" value="{{ old($field,auth('web')->user()?->{['customer_name'=>'name','customer_email'=>'email','customer_phone'=>'phone'][$field]}) }}" required></label>@endforeach
@if($delivery==='shipping')
<fieldset class="space-y-2 rounded border p-4"><legend>{{ __('store::store.shipping_address') }}</legend>
@foreach(['line1'=>'address','city'=>'city','postal_code'=>'postal_code','country'=>'country_code_tr'] as $field=>$label)<label class="block">{{ __('store::store.'.$label) }}<input class="ml-2 rounded border p-2" name="address[{{ $field }}]" value="{{ old('address.'.$field,$field==='country'?'TR':'') }}" @required($field!=='postal_code')></label>@endforeach
</fieldset>
@endif
<details><summary>{{ __('store::store.optional_billing_address') }}</summary>@foreach(['line1'=>'address','city'=>'city','postal_code'=>'postal_code','country'=>'country_code'] as $field=>$label)<label class="block">{{ __('store::store.'.$label) }}<input class="rounded border p-2" name="billing_address[{{ $field }}]" value="{{ old('billing_address.'.$field) }}"></label>@endforeach</details>
@include('store::public.summary',['quote'=>$quote])
<p>{{ $instructions }}</p><p>{{ __('store::store.payment_pending_notice') }}</p>
<button class="rounded bg-green-700 p-3 text-white">{{ __('store::store.confirm_create_order') }}</button>
</form>
@endsection
