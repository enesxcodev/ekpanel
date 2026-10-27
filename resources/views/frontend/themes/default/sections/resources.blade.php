@if($resources->isNotEmpty())
    <section id="bilgi-merkezi" class="section-space !pb-8">
        <div class="site-container">
            <p class="eyebrow">{{ $copy['resources_eyebrow'] }}</p>
            <h2 class="section-title mt-2">{{ $copy['resources_title'] }}</h2>
            <div class="mt-7 grid gap-5 md:grid-cols-3">
                @foreach($resources as $resource)
                    <article class="content-card flex items-start gap-4 !p-5">
                        <span class="icon-tile !size-10 shrink-0"><x-frontend.default.icon :name="$resource['icon']" class="size-5" /></span>
                        <div>
                            <span class="badge">{{ $resource['label'] }}</span>
                            <h3 class="mt-3 text-sm leading-5 font-extrabold">{{ $resource['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $resource['description'] }}</p>
                            @if($resource['url'])
                                <a href="{{ $resource['url'] }}" class="text-link mt-4">{{ $resource['action'] }} →</a>
                            @elseif($contactAvailable)
                                @if($contactAvailable)<a href="#iletisim" class="text-link mt-4">{{ $copy['resources_action_label'] }} →</a>@endif
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
