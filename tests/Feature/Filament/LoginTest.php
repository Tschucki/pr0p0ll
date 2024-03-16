<?php

declare(strict_types=1);

it('has filament login page', function () {
    $response = $this->get(route('filament.pr0p0ll.auth.login'));
    $response->assertStatus(200);
});
