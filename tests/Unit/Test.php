<?php

declare(strict_types=1);

use App\Models\User;

it('returns demographic data as array', function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed');
    $data = User::first()->getDemographicData();
    $this->assertIsArray($data);
});
