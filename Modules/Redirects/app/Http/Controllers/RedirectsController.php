<?php

namespace Modules\Redirects\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class RedirectsController extends Controller
{
    public function index(): View
    {
        return view('redirects::index');
    }
}
