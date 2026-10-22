<?php

namespace Modules\Appointments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AppointmentsController extends Controller
{
    public function index(): View
    {
        return view('appointments::index');
    }
}
