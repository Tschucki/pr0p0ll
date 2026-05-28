<?php

declare(strict_types=1);
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DuskTestCase;
use Tests\TestCase;

uses(
    DuskTestCase::class,
    DatabaseMigrations::class,
)->in('Browser');

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Feature');

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Unit');
