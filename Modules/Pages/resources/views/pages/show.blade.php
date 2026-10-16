<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }} · {{ config('app.name') }}</title>
    <style>
        .page-content { line-height: 1.75; }
        .page-content :is(h1, h2, h3, h4, h5, h6) { margin-block: 1.5em .5em; font-weight: 600; line-height: 1.25; }
        .page-content :is(p, ul, ol, blockquote, table, pre) { margin-block: 1em; }
        .page-content :is(ul, ol) { padding-inline-start: 1.5em; }
        .page-content ul { list-style: disc; }
        .page-content ol { list-style: decimal; }
        .page-content a { text-decoration: underline; }
        .page-content blockquote { border-inline-start: 3px solid #cbd5e1; padding-inline-start: 1em; }
        .page-content table { border-collapse: collapse; width: 100%; }
        .page-content :is(th, td) { border: 1px solid #cbd5e1; padding: .5em; }
        .page-content pre { overflow-x: auto; padding: 1em; background: #f1f5f9; }
    </style>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body>
    <main class="mx-auto max-w-4xl px-6 py-12">
        @includeIf('menus::public.navigation', ['items' => $navigationItems ?? []])
        <nav aria-label="İçerik yolu" class="mb-6 text-sm">
            <a href="{{ url('/') }}">Ana sayfa</a>
            <span aria-hidden="true"> / </span>
            @if ($page->category)
                <span>{{ $page->category->name }}</span>
            @else
                <span>{{ $page->title }}</span>
            @endif
        </nav>
        <article>
            <h1 class="mb-4 text-4xl font-semibold">{{ $page->title }}</h1>
            @if ($page->short_description)
                <p class="mb-6 text-lg">{{ $page->short_description }}</p>
            @endif
            @if ($page->image)
                <img class="mb-8 h-auto max-w-full rounded-lg" src="{{ asset('storage/'.$page->image) }}" alt="{{ $page->title }}">
            @endif
            <div class="page-content">{!! $safeContent !!}</div>
        </article>
    </main>
</body>
</html>
