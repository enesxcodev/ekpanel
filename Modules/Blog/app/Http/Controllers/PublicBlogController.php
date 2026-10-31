<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\HtmlSanitizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Blog\Services\BlogFrontendService;
use Modules\Blog\Services\BlogLanguageService;

class PublicBlogController extends Controller
{
    public function index(Request $request, BlogLanguageService $languages, BlogFrontendService $frontend): View|RedirectResponse
    {
        $language = $languages->forPublic($request->route('lang'));
        if ($request->route('lang') === BlogLanguageService::PrimaryCode) {
            return redirect()->route('blog.public.index', $request->query(), 301);
        }
        $request->attributes->set('blog_language', $language->code);
        app()->setLocale($language->code);
        $contents = $frontend->paginate(12, null, $language->code);
        abort_if($contents === null, 404);

        return view('blog::public.index', ['contents' => $contents, 'blogLanguage' => $language->code, 'blogLanguages' => $languages]);
    }

    public function show(Request $request, BlogLanguageService $languages, BlogFrontendService $frontend, HtmlSanitizer $sanitizer): View|RedirectResponse
    {
        $language = $languages->forPublic($request->route('lang'));
        $slug = (string) $request->route('slug');
        if ($request->route('lang') === BlogLanguageService::PrimaryCode) {
            return redirect()->route('blog.public.show', ['slug' => $slug], 301);
        }
        $request->attributes->set('blog_language', $language->code);
        app()->setLocale($language->code);
        $content = $frontend->findBySlug($slug, $language->code);
        abort_if($content === null, 404);

        return view('blog::public.show', ['content' => $content, 'blogLanguage' => $language->code, 'safeContent' => $sanitizer->sanitize((string) $content->content)]);
    }
}
