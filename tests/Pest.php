<?php

declare(strict_types=1);

uses(
    Tests\DuskTestCase::class,
    Illuminate\Foundation\Testing\DatabaseMigrations::class,
)->in('Browser');

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Unit');
