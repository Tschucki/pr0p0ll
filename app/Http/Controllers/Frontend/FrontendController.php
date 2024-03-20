<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Polls\Poll;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FrontendController extends Controller
{
    public function landing(): Response
    {
        return Inertia::render('Frontend/Landing', [
            'userCount' => (string) User::count(),
            'pollCount' => (string) Poll::count(),
        ]);
    }

    public function imprint(): Response
    {
        $sImprintPath = resource_path('markdown/imprint.md');
        $sImprintMarkdown = Str::markdown(file_get_contents($sImprintPath));

        return Inertia::render('Frontend/Imprint', [
            'imprint' => $sImprintMarkdown,
        ]);
    }

    public function privacy(): Response
    {
        $sPrivacyPath = resource_path('markdown/privacy.md');
        $sPrivacyMarkdown = Str::markdown(file_get_contents($sPrivacyPath));

        return Inertia::render('Frontend/Privacy', [
            'privacy' => $sPrivacyMarkdown,
        ]);
    }

    public function terms(): Response
    {
        $sTermsPath = resource_path('markdown/terms.md');
        $sTermsMarkdown = Str::markdown(file_get_contents($sTermsPath));

        return Inertia::render('Frontend/Terms', [
            'terms' => $sTermsMarkdown,
        ]);
    }
}
