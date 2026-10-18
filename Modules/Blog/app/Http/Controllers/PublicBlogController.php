<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\HtmlSanitizer;
use Illuminate\Contracts\View\View;
use Modules\Blog\Models\BlogContent;

class PublicBlogController extends Controller
{
    public function index(): View
    {
        $contents = BlogContent::query()->where('status', 'published')->with(['category', 'author', 'imageMedia', 'tags'])->latest('published_at')->paginate(12);

        return view('blog::public.index', compact('contents'));
    }

    public function show(string $slug, HtmlSanitizer $sanitizer): View
    {
        $content = BlogContent::query()->where('slug', $slug)->where('status', 'published')->with(['category', 'author', 'imageMedia', 'tags'])->firstOrFail();

        return view('blog::public.show', ['content' => $content, 'safeContent' => $sanitizer->sanitize((string) $content->content)]);
    }
}
