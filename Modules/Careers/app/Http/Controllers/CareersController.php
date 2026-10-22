<?php

namespace Modules\Careers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class CareersController extends Controller
{
    public function index(): View
    {
        return view('careers::index');
    }
}
