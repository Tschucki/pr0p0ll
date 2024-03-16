<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

test('leaderboard works', function () {
    Artisan::call('db:seed');

    $this->browse(function (Browser $browser) {
        $browser->loginAs(\App\Models\User::first());

        $browser->visit('/pr0p0ll')
            ->assertSee('Dashboard')
            ->clickLink('Leaderboard')
            ->waitFor('header h1')
            ->assertPathIs('/pr0p0ll/leaderboard')
            ->assertSee('Leaderboard');
    });
});
