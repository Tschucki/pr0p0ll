<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class FrontendController extends Controller
{
    public function landing()
    {
        return Inertia::render('Frontend/Landing');
    }
}
