<a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 text-2xl font-extrabold tracking-tight" aria-label="{{ $site['name'] }} ana sayfa">
    @if($logo)
        <img src="{{ $logo }}" alt="{{ $site['name'] }}" class="h-9 w-auto max-w-44 object-contain" width="150" height="36">
    @else
        <span class="inline-block size-5 rounded-[3px] border-[3px] border-indigo-600" aria-hidden="true"></span>
        <span>{{ $site['name'] }}</span>
    @endif
</a>
