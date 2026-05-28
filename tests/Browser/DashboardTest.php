<?php

declare(strict_types=1);

use App\Models\User;
use Facebook\WebDriver\Exception\TimeoutException;
use Laravel\Dusk\Browser;

test('filament redirects if not logged in', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/pr0p0ll')
            ->assertSee('Mit pr0gramm anmelden');
    });
});

test('dashboard works', function () {
    Artisan::call('db:seed');

    $this->browse(/**
     * @throws TimeoutException
     */ function (Browser $browser) {
        $browser->loginAs(User::first());

        $browser->visit('/pr0p0ll')
            ->assertSee('Dashboard')
            ->waitFor('section h2')
            ->assertSee('Daten aktualisieren');
    });
});
