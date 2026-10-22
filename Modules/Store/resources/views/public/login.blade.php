@extends('store::public.layout')
@section('store-content')
<h1 class="mb-4 text-3xl">Hesabınızla giriş yapın</h1>
@if(!$users)<p>Hesapla checkout şu anda kullanılamıyor. Mağaza yöneticisi Users modülünü etkinleştirmeli veya misafir checkout'u açmalıdır.</p>
@else
<p>Bu mağazada sipariş vermek için hesabınızla giriş yapmalısınız. Sepetiniz korunur.</p>
<form data-store-login action="{{ route('users.login') }}" method="POST" class="space-y-3">@csrf
<label class="block">E-posta <input name="email" type="email" class="rounded border p-2" required></label><label class="block">Şifre <input name="password" type="password" class="rounded border p-2" required></label><button class="rounded border p-2">Giriş yap</button><p data-login-error role="alert"></p></form>
@endif
@endsection
@push('scripts')
@if($users)<script>
document.querySelector('[data-store-login]').addEventListener('submit',async event=>{
event.preventDefault(); const form=event.currentTarget;
try { const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json'}});
const data=await response.json(); if(!response.ok) throw new Error(data.message || 'Giriş başarısız.'); location.href=@json(route('store.checkout.form')); }
catch(error){form.querySelector('[data-login-error]').textContent=error.message;}
});
</script>@endif
@endpush
