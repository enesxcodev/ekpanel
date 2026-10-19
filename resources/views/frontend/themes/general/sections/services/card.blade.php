<article class="rounded-xl border border-white/[0.08] bg-gradient-to-br from-white/[0.045] to-white/[0.015] p-5 transition hover:border-[#e43b47]/40">
    <span class="mb-3 grid size-14 place-items-center rounded-full bg-[#e43b47]/10 text-[#f04450]">@include('frontend.themes.general.sections.services.icon', ['name' => $service->icon])</span>
    <h3 class="font-bold">{{ $service->title }}</h3>
    @if ($service->short_description)
        <p class="mt-1 text-sm leading-relaxed text-slate-400">{{ $service->short_description }}</p>
    @endif
</article>
