<?php

declare(strict_types=1);

use Illuminate\Support\Facades\URL;

it('renders the standalone evaluation for a signed url', function () {
    $poll = makeClosedPoll();

    $single = addQuestion($poll, 'radio', [['title' => 'Eine sehr lange Antwortmöglichkeit zum Testen des Umbruchs']]);
    addAnswer($single, 'Eine sehr lange Antwortmöglichkeit zum Testen des Umbruchs');

    $text = addQuestion($poll, 'text');
    addAnswer($text, 'Mein Freitext-Kommentar');

    $url = URL::signedRoute('poll.results.render', ['poll' => $poll->getKey()]);

    $this->get($url)
        ->assertOk()
        ->assertSee('pr0p0ll Umfrageauswertung')
        ->assertSee('Test Umfrage')
        ->assertSee('Teilnehmer-Informationen')
        ->assertSee('Mein Freitext-Kommentar');
});

it('rejects an unsigned url', function () {
    $poll = makeClosedPoll();

    $this->get(route('poll.results.render', ['poll' => $poll->getKey()]))
        ->assertForbidden();
});

it('returns 404 for a poll that has not ended', function () {
    $poll = makeClosedPoll();
    $poll->update(['published_at' => now(), 'closes_at' => now()->addWeek()]);

    $url = URL::signedRoute('poll.results.render', ['poll' => $poll->getKey()]);

    $this->get($url)->assertNotFound();
});

it('does not render the creator name in the screenshot view for anonymous polls', function () {
    $poll = makeClosedPoll(); // makeClosedPoll erstellt not_anonymous = false

    $url = URL::signedRoute('poll.results.render', ['poll' => $poll->getKey()]);

    $this->get($url)
        ->assertOk()
        ->assertDontSee('Erstellt von:')
        ->assertDontSee($poll->user->name);
});

it('renders the creator name in the screenshot view for non-anonymous polls', function () {
    $poll = makeClosedPoll();
    $poll->update(['not_anonymous' => true]);

    $url = URL::signedRoute('poll.results.render', ['poll' => $poll->getKey()]);

    $this->get($url)
        ->assertOk()
        ->assertSee('Erstellt von:')
        ->assertSee($poll->user->name);
});
