<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection === 'sqlite' && !extension_loaded('pdo_sqlite')) {
            $this->fail('Driver pdo_sqlite no disponible. Todos los tests deben usar BD en memoria.');
        }

        if (in_array($connection, ['mysql', 'mysql2']) && in_array($database, ['faristol', 'web2'])) {
            $this->fail("BD producción ($database) detectada. Los tests NUNCA deben usar la BD real.");
        }
    }
}
