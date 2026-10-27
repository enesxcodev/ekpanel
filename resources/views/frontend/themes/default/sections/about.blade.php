@if($about['title'] || $about['paragraphs'] || $about['values'])
<section id="kurumsal" class="section-space border-t border-slate-100">
    <div class="site-container grid gap-10 lg:grid-cols-2 lg:gap-20">
        <div>
            <p class="eyebrow">{{ $copy['about_eyebrow'] }}</p>
            <h2 class="section-title mt-3">{{ $about['title'] }}</h2>
            <div class="mt-5 flex max-w-lg flex-col gap-4 text-sm leading-7 text-slate-500">
                @foreach($about['paragraphs'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
            </div>
            @if($copy['about_action_url'] && $copy['about_action_label'])<a href="{{ $copy['about_action_url'] }}" class="text-link mt-6">{{ $copy['about_action_label'] }} <span aria-hidden="true">→</span></a>@endif
        </div>
        <div id="degerlerimiz">
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($about['values'] as $value)
                    <div class="rounded-xl border border-slate-200/80 bg-white p-5">
                        <span class="icon-tile !size-10"><x-frontend.default.icon :name="$value['icon']" class="size-5" /></span>
                        <h3 class="mt-3 text-sm font-extrabold">{{ $value['title'] }}</h3>
                        <p class="mt-1.5 text-sm leading-5 text-slate-500">{{ $value['description'] }}</p>
                    </div>
                @endforeach
            </div>
            @if($copy['values_action_url'] && $copy['values_action_label'])<a href="{{ $copy['values_action_url'] }}" class="text-link mt-4">{{ $copy['values_action_label'] }} <span aria-hidden="true">→</span></a>@endif
        </div>
    </div>
</section>

@endif
