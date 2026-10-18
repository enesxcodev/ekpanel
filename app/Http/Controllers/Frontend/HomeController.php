<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\HomepageViewDataBuilder;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(HomepageViewDataBuilder $homepageViewDataBuilder): View
    {
        return view('frontend.themes.general.index', $homepageViewDataBuilder->build());
    }
}
