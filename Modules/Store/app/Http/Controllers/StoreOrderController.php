<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class StoreOrderController extends Controller
{
    public function index(): View
    {
        return view('store::index');
    }
}
