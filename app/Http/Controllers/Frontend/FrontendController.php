<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Polls\Poll;
use App\Models\User;
use Inertia\Inertia;

class FrontendController extends Controller
{
    public function landing()
    {
        return Inertia::render('Frontend/Landing', [
            'userCount' => User::count(),
            'pollCount' => Poll::count()
        ]);
    }
}
