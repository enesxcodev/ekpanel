@if($brands->isNotEmpty())
    <section class="border-b border-slate-100 py-7" aria-label="{{ $copy['brands_title'] }}">
        <div class="site-container">
            <p class="text-xs font-semibold text-slate-500">{{ $copy['brands_title'] }}</p>
            <div class="mt-5 grid grid-cols-2 items-center gap-6 sm:grid-cols-3 lg:grid-cols-5">
                @foreach($brands as $brand)
                    <div class="flex min-h-10 items-center justify-center text-2xl font-bold tracking-tight text-slate-400">
                        @if($brand['url'])<a href="{{ $brand['url'] }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-indigo-600">@endif
                        @if($brand['image'])
                            <img src="{{ $brand['image'] }}" alt="{{ $brand['name'] }}" width="140" height="40" class="h-10 max-w-36 object-contain grayscale" loading="lazy">
                        @else
                            {{ $brand['name'] }}
                        @endif
                        @if($brand['url'])</a>@endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
