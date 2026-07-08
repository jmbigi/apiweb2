<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        $usesDatabaseTraits = collect(class_uses_recursive(static::class))
            ->intersect([
                \Illuminate\Foundation\Testing\DatabaseTransactions::class,
                \Illuminate\Foundation\Testing\RefreshDatabase::class,
                \Illuminate\Foundation\Testing\DatabaseMigrations::class,
            ])
            ->isNotEmpty();

        if ($usesDatabaseTraits) {
            $dbConnection = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'mysql');
            if ($dbConnection === 'sqlite' && !extension_loaded('pdo_sqlite')) {
                $this->fail('Driver pdo_sqlite no disponible. Tests con BD requieren pdo_sqlite.');
            }
        }

        parent::setUp();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection === 'sqlite' && !extension_loaded('pdo_sqlite') && $usesDatabaseTraits) {
            $this->fail('Driver pdo_sqlite no disponible. Tests con BD requieren pdo_sqlite.');
        }

        if (in_array($connection, ['mysql', 'mysql2']) && in_array($database, ['faristol', 'web2'])) {
            $this->fail("BD producción ($database) detectada. Los tests NUNCA deben usar la BD real.");
        }
    }
}
