@extends('store::public.layout')
@section('store-content')
<h1 class="mb-4 text-3xl">{{ __('store::store.login_title') }}</h1>
@if(!$users)<p>{{ __('store::store.login_unavailable') }}</p>
@else
<p>{{ __('store::store.login_required') }}</p>
<form data-store-login action="{{ route('users.login') }}" method="POST" class="space-y-3">@csrf
<label class="block">{{ __('store::store.email') }} <input name="email" type="email" class="rounded border p-2" required></label><label class="block">{{ __('store::store.password') }} <input name="password" type="password" class="rounded border p-2" required></label><button class="rounded border p-2">{{ __('store::store.login') }}</button><p data-login-error role="alert"></p></form>
@endif
@endsection
@push('scripts')
@if($users)<script>
document.querySelector('[data-store-login]').addEventListener('submit',async event=>{
event.preventDefault(); const form=event.currentTarget;
try { const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json'}});
const data=await response.json(); if(!response.ok) throw new Error(data.message || @json(__('store::store.login_failed'))); location.href=@json(route('store.checkout.form',['lang'=>request()->route('lang') ?? request('lang','tr')])); }
catch(error){form.querySelector('[data-login-error]').textContent=error.message;}
});
</script>@endif
@endpush
