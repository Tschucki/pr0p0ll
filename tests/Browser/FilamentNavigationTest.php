<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

test('navigation is present', function () {
    Artisan::call('db:seed');

    $this->browse(function (Browser $browser) {
        $browser->loginAs(\App\Models\User::first());

        $browser->visit('/pr0p0ll')
            ->assertSee('Dashboard')
            ->assertSeeLink('Leaderboard')
            ->assertSeeLink('Meine Umfragen')
            ->assertSeeLink('Öffentliche Umfragen')
            ->assertSeeLink('Einstellungen')
            ->assertSeeLink('FAQ');
    });
});
