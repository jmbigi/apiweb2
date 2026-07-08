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

        if ($usesDatabaseTraits && !extension_loaded('pdo_sqlite')) {
            $dbConnection = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'mysql');
            if ($dbConnection === 'sqlite') {
                $this->markTestSkipped('Driver pdo_sqlite no disponible en este servidor.');
            }
        }

        parent::setUp();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if (in_array($connection, ['mysql', 'mysql2']) && in_array($database, ['faristol', 'web2'])) {
            $this->markTestSkipped("BD producción ($database). Tests saltados.");
        }
    }
}
