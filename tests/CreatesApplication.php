<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->guardAgainstNonTestDatabase($app);

        return $app;
    }

    // Sicherheitsnetz: bricht ab, falls Tests gegen eine Nicht-Test-DB laufen würden.
    private function guardAgainstNonTestDatabase(Application $app): void
    {
        $database = (string) $app['db']->connection()->getDatabaseName();
        $isTestDatabase = $database === ':memory:' || str_contains($database, 'test');

        if (! $isTestDatabase) {
            throw new \RuntimeException(
                "Refusing to run tests against non-test database [{$database}]. ".
                'Set DB_DATABASE to a *_testing database (phpunit.xml).'
            );
        }
    }
}
