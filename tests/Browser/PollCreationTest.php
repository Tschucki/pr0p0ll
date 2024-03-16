<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;

test('poll creation works', function () {
    Artisan::call('db:seed');

    $this->browse(function (Browser $browser) {
        $browser->loginAs(\App\Models\User::first());

        $browser->visit('/pr0p0ll/my-polls');
        $browser->clickLink('Erstellen');
        $browser->visit('/pr0p0ll/my-polls/create');
        $browser->type('input#data\.title', 'Neuer Poll');
        $browser->type('textarea#data\.description', 'Tolle Beschreibung');
        $browser->press('Zielgruppe');
        $browser->press('Fragen');
        $browser->press('Hinzufügen');
        $browser->press('Ja - Nein');
        $browser->waitFor('li h4');
        $browser->type('li input.fi-input', 'Testfrage?');
        $browser->press('Erstellen');
        $browser->waitFor('h1');
        $browser->assertSee('Meine Umfrage ansehen');
        $browser->assertPathIs('/pr0p0ll/my-polls/2');
    });
});
