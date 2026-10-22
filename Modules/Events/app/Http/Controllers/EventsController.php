<?php

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class EventsController extends Controller
{
    public function index(): View
    {
        return view('events::index');
    }
}
