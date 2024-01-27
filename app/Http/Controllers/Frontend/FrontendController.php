<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Polls\Poll;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FrontendController extends Controller
{
    public function landing(): \Inertia\Response
    {
        return Inertia::render('Frontend/Landing', [
            'userCount' => User::count(),
            'pollCount' => Poll::count()
        ]);
    }

    public function imprint(): \Inertia\Response
    {
        $sImprintPath = resource_path('markdown/imprint.md');
        $sImprintMarkdown = Str::markdown(file_get_contents($sImprintPath));

        return Inertia::render('Frontend/Imprint', [
            'imprint' => $sImprintMarkdown
        ]);
    }

    public function privacy(): \Inertia\Response
    {
        $sPrivacyPath = resource_path('markdown/privacy.md');
        $sPrivacyMarkdown = Str::markdown(file_get_contents($sPrivacyPath));

        return Inertia::render('Frontend/Privacy', [
            'privacy' => $sPrivacyMarkdown
        ]);
    }

    public function terms(): \Inertia\Response
    {
        $sTermsPath = resource_path('markdown/terms.md');
        $sTermsMarkdown = Str::markdown(file_get_contents($sTermsPath));

        return Inertia::render('Frontend/Terms', [
            'terms' => $sTermsMarkdown
        ]);
    }
}
