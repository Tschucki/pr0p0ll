<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->redirectUsersTo('/pr0p0ll');
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('app:login-to-pr0gramm')->hourly();
        $schedule->command('ban:delete-expired')->everyMinute();
        $schedule->command('app:post-poll-result')->dailyAt('19:45')->timezone('Europe/Berlin')->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
