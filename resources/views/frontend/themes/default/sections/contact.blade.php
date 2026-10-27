@if($contactAvailable)
<section id="iletisim" class="section-space bg-slate-50">
    <div class="site-container grid items-start gap-10 lg:grid-cols-2 lg:gap-20">
        <div>
            <p class="eyebrow">{{ $copy['contact_eyebrow'] }}</p>
            <h2 class="section-title mt-2">{{ $copy['contact_title'] }}</h2>
            <p class="mt-4 max-w-md text-sm leading-7 text-slate-500">{{ $copy['contact_description'] }}</p>
            <div class="mt-6 flex flex-col items-start gap-3 text-sm font-bold">
                @if($site['phone'])<a href="{{ $site['phone_url'] }}" class="inline-flex items-center gap-3"><x-frontend.default.icon name="phone" class="size-5 text-indigo-600" />{{ $site['phone'] }}</a>@endif
                @if($site['email'])<a href="mailto:{{ $site['email'] }}" class="inline-flex items-center gap-3 break-all"><x-frontend.default.icon name="mail" class="size-5 shrink-0 text-indigo-600" />{{ $site['email'] }}</a>@endif
            </div>
            @if($appointmentAvailable && $contactFormKey && $copy['contact_appointment_label'])
                <a href="#iletisim-formu" class="button-secondary mt-6">{{ $copy['contact_appointment_label'] }}</a>
            @endif
        </div>
        @if($contactFormKey)
        <div id="iletisim-formu" class="rounded-xl border border-slate-200/70 bg-white p-6 sm:p-7">
            @if($contactFormKey)
                <x-site-form :key="$contactFormKey" :unstyled="true" class="default-contact-form" />
            @endif
        </div>
        @endif
    </div>
</section>

@endif
