@if ($otherServices->isNotEmpty())
<div class="mt-4 flex items-start gap-4 rounded-xl border border-white/[0.08] bg-white/[0.025] p-5 sm:items-center">
    <span class="grid size-12 shrink-0 place-items-center rounded-full bg-[#e43b47]/10 text-[#f04450]"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 1 1.7-2 3.4-2 .1-.9-1.6h-3l-.9 1.6-2-.1-2-3.4 1-1.7-1.5-2.6-1.9-.1v-4l1.9-.1 1.5-2.6-1-1.7 2-3.4 2-.1.9 1.6h3l.9-1.6 2 .1 2 3.4-1 1.7 1.5 2.6 1.9.1v4l-1.9.1z"/></svg></span>
    <div><h3 class="font-bold">{{ $servicesCopy['other_title'] }}</h3><p class="mt-1 flex flex-wrap gap-x-2 text-sm leading-relaxed text-slate-400">@foreach ($otherServices as $service)<span>{{ $service->title }}</span>@if (! $loop->last)<span class="text-[#f04450]">•</span>@endif @endforeach</p></div>
</div>
@endif
