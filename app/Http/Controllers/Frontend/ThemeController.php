<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\HomePageService;
use Illuminate\Contracts\View\View;

class ThemeController extends Controller
{
    public function __construct(private HomePageService $homePageService) {}

    public function index(): View
    {
        return view('frontend.themes.default.index', $this->homePageService->index());
    }
}
