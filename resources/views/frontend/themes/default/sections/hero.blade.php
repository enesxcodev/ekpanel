@if($hero['title'])
<section class="bg-slate-50">
    <div class="site-container grid items-center gap-10 py-12 lg:grid-cols-2 lg:gap-16 lg:py-14">
        <div>
            <p class="eyebrow">{{ $hero['eyebrow'] }}</p>
            <h1 class="mt-4 max-w-xl text-[2.65rem] leading-[1.12] font-extrabold tracking-[-0.045em] sm:text-5xl lg:text-[3.4rem]">{{ $hero['title'] }}</h1>
            <p class="mt-5 max-w-lg text-base leading-7 text-slate-500">{{ $hero['description'] }}</p>
            <div class="mt-7 flex flex-wrap gap-3">
                @if($hero['primary_url'] && $hero['primary_label'])<a href="{{ $hero['primary_url'] }}" class="button-primary">{{ $hero['primary_label'] }}</a>@endif
                @if($hero['secondary_url'] && $hero['secondary_label'])<a href="{{ $hero['secondary_url'] }}" class="button-secondary">{{ $hero['secondary_label'] }}</a>@endif
            </div>
            <div class="mt-8 flex flex-wrap gap-x-6 gap-y-4">
                @foreach($advantages as $advantage)
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <span class="icon-tile !size-9 !rounded-lg"><x-frontend.default.icon :name="$advantage['icon']" class="size-5" /></span>
                        {{ $advantage['title'] }}
                    </div>
                @endforeach
            </div>
        </div>
        @if($hero['image'])<img src="{{ $hero['image'] }}" alt="{{ $hero['title'] }}" width="640" height="448" class="aspect-[1.43] w-full rounded-lg object-cover" fetchpriority="high">@endif
    </div>
</section>

@endif
