@if($opportunities->isNotEmpty())
    <section class="pb-12" aria-label="{{ $copy['opportunities_eyebrow'] }}">
        <div class="site-container">
            <p class="eyebrow">{{ $copy['opportunities_eyebrow'] }}</p>
            <div class="mt-3 grid gap-5 md:grid-cols-3">
                @foreach($opportunities as $opportunity)
                    <article class="content-card flex items-start gap-4 !p-5">
                        <span class="icon-tile !size-10 shrink-0"><x-frontend.default.icon :name="$opportunity['icon']" class="size-5" /></span>
                        <div>
                            <h2 class="text-sm font-extrabold">{{ $opportunity['title'] }}</h2>
                            @if($opportunity['subtitle'])<p class="mt-2 text-sm font-semibold">{{ $opportunity['subtitle'] }}</p>@endif
                            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $opportunity['description'] }}</p>
                            @if($opportunity['url'] || $contactAvailable)<a href="{{ $opportunity['url'] ?: '#iletisim' }}" class="text-link mt-4">{{ $copy['opportunities_action_label'] }} →</a>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
