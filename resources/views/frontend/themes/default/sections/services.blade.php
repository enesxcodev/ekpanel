@if($services->isNotEmpty() || $sectors->isNotEmpty())
    <section id="hizmetler" class="section-space">
        <div class="site-container">
            @if($services->isNotEmpty())
                <div class="section-heading">
                    <h2>{{ $copy['services_title'] }}</h2>
                    @if($copy['services_action_url'] && $copy['services_action_label'])<a href="{{ $copy['services_action_url'] }}" class="text-link">{{ $copy['services_action_label'] }} <span aria-hidden="true">→</span></a>@endif
                </div>
                <div class="mt-7 grid items-start gap-5 md:grid-cols-3">
                    @foreach($services as $service)
                        <article class="content-card">
                            <span class="icon-tile"><x-frontend.default.icon :name="$service['icon']" /></span>
                            <h3 class="mt-4 text-lg font-extrabold tracking-tight">{{ $service['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $service['description'] }}</p>
                            @if($service['detail'])
                                <details class="card-detail mt-5">
                                    <summary class="text-link cursor-pointer">{{ $copy['services_detail_label'] }} <span aria-hidden="true">→</span></summary>
                                    <p class="mt-3 text-sm leading-6 text-slate-500">{{ $service['detail'] }}</p>
                                    @if($copy['services_action_url'] && $copy['services_action_label'])<a href="{{ $copy['services_action_url'] }}" class="text-link mt-3">{{ $copy['services_action_label'] }} →</a>@endif
                                </details>
                            @else
                                @if($copy['services_action_url'] && $copy['services_action_label'])<a href="{{ $copy['services_action_url'] }}" class="text-link mt-5">{{ $copy['services_action_label'] }} →</a>@endif
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
            @if($sectors->isNotEmpty())
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <p class="mr-3 text-xs font-semibold text-slate-500">{{ $copy['sectors_title'] }}</p>
                    @foreach($sectors as $sector)
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-5 py-2.5 text-xs font-medium text-slate-600"><x-frontend.default.icon :name="$sector['icon']" class="size-4" />{{ $sector['title'] }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
