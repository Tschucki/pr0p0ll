<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('returns demographic data as array', function () {
    Artisan::call('db:seed');
    Artisan::call('db:seed UserSeeder');
    $data = User::first()->getDemographicData();
    $this->assertIsArray($data);
});
