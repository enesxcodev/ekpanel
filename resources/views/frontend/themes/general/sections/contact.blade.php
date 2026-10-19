<div id="iletisim" class="mt-6 grid gap-3 md:grid-cols-3">
    <article class="relative overflow-hidden rounded-xl border border-white/[0.08] bg-gradient-to-br from-white/[0.045] to-white/[0.015] p-5">
        <svg class="mb-4 size-7 text-[#f04450]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        <h3 class="font-bold">Çalışma Saatleri</h3><p class="mt-1 whitespace-pre-line text-sm text-slate-400">{{ $workingHours ?: 'Çalışma saatleri için lütfen iletişime geçin.' }}</p>
    </article>
    <article class="relative overflow-hidden rounded-xl border border-white/[0.08] bg-gradient-to-br from-white/[0.045] to-white/[0.015] p-5">
        <svg class="mb-4 size-7 text-[#f04450]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
        <h3 class="font-bold">Konumumuz</h3><p class="mt-1 whitespace-pre-line text-sm text-slate-400">{{ $address ?: 'Haritada görüntüleyin ve yol tarifi alın.' }}</p>
        <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-[#e43b47]/60 px-3.5 py-2 text-xs font-bold transition hover:bg-[#e43b47]/10"><span class="text-[#ff515c]">⌖</span> {{ $heroDirectionsText }} <span class="text-[#ff515c]">→</span></a>
    </article>
    <article class="relative overflow-hidden rounded-xl border border-white/[0.08] bg-gradient-to-br from-white/[0.045] to-white/[0.015] p-5">
        <svg class="mb-4 size-7 text-[#f04450]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 9 9 0 0 1-4-.9L3 21l1.7-4.7A8.4 8.4 0 1 1 21 11.5z"/></svg>
        <h3 class="font-bold">İletişim</h3><p class="mt-1 text-sm text-slate-400">Randevu ve bilgi için<br>bize ulaşın.</p>
        @if ($whatsAppUrl)
        <a href="{{ $whatsAppUrl }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-[#e43b47] to-[#ba2632] px-3.5 py-2.5 text-xs font-bold text-white"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.6A8.5 8.5 0 1 1 20.5 11.5Z"/><path d="M8 8.3c.4 2.8 2.6 5 5.5 5.7l1.4-1.4 2 .8"/></svg> WhatsApp ile İletişim <span>→</span></a>
        @endif
    </article>
</div>
