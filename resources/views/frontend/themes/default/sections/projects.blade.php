@if($projects->isNotEmpty())
    <section id="projeler" class="section-space">
        <div class="site-container">
            <div class="section-heading">
                <div><p class="eyebrow">{{ $copy['projects_eyebrow'] }}</p><h2 class="mt-2">{{ $copy['projects_title'] }}</h2></div>
                @if($copy['projects_action_url'] && $copy['projects_action_label'])<a href="{{ $copy['projects_action_url'] }}" class="text-link">{{ $copy['projects_action_label'] }} <span aria-hidden="true">→</span></a>@endif
            </div>
            <div class="mt-7 grid items-start gap-5 md:grid-cols-3">
                @foreach($projects as $project)
                    <article class="overflow-hidden rounded-xl border border-slate-200/80 bg-white">
                        @if($project['image'])
                            <img src="{{ $project['image'] }}" alt="{{ $project['title'] }}" width="400" height="220" class="aspect-[1.9] w-full object-cover" loading="lazy">
                        @else
                            <div class="flex aspect-[1.9] items-center justify-center bg-slate-50 text-slate-300" aria-hidden="true"><x-frontend.default.icon name="photo" class="size-12" /></div>
                        @endif
                        <div class="p-5">
                            @if($project['category'])<span class="badge">{{ $project['category'] }}</span>@endif
                            <h3 class="mt-3 text-base font-extrabold">{{ $project['title'] }}</h3>
                            @if($project['description'])
                                <details class="card-detail mt-4">
                                    <summary class="text-link cursor-pointer">{{ $copy['projects_detail_label'] }} <span aria-hidden="true">→</span></summary>
                                    <p class="mt-3 text-sm leading-6 text-slate-500">{{ $project['description'] }}</p>
                                </details>
                            @else
                                @if($copy['projects_action_url'] && $copy['projects_action_label'])<a href="{{ $copy['projects_action_url'] }}" class="text-link mt-4">{{ $copy['projects_action_label'] }} →</a>@endif
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
