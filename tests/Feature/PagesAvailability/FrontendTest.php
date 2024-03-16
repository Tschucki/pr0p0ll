<?php

declare(strict_types=1);

it('has landingpage page', function () {
    $response = $this->get(route('frontend.landing'));
    $response->assertStatus(200);
});

it('has imprint page', function () {
    $response = $this->get(route('frontend.imprint'));
    $response->assertStatus(200);
});

it('has privacy page', function () {
    $response = $this->get(route('frontend.privacy'));
    $response->assertStatus(200);
});

it('has terms page', function () {
    $response = $this->get(route('frontend.terms'));
    $response->assertStatus(200);
});
