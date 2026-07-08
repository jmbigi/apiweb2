<?php

namespace Tests\Unit;

use Tests\TestCase;

class SafetyAppTest extends TestCase
{
    private function requiresPdoSqlite(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite no disponible en este servidor');
        }
    }

    public function test_config_not_cached(): void
    {
        $this->requiresPdoSqlite();
        $this->assertFalse(app()->configurationIsCached());
    }

    public function test_routes_not_cached(): void
    {
        $this->requiresPdoSqlite();
        $this->assertFalse(app()->routesAreCached());
    }

    public function test_environment_is_testing(): void
    {
        $this->requiresPdoSqlite();
        $this->assertEquals('testing', app()->environment());
    }

    public function test_database_is_sqlite_memory(): void
    {
        $this->requiresPdoSqlite();
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $this->assertEquals('sqlite', $connection);
        $this->assertEquals(':memory:', $database);
    }

    public function test_database_is_not_production(): void
    {
        $this->requiresPdoSqlite();
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $isProduction = in_array($connection, ['mysql', 'mysql2'])
            && in_array($database, ['faristol', 'web2']);
        $this->assertFalse($isProduction);
    }

    public function test_cache_driver_is_array(): void
    {
        $this->requiresPdoSqlite();
        $this->assertEquals('array', config('cache.default'));
    }

    public function test_queue_driver_is_sync(): void
    {
        $this->requiresPdoSqlite();
        $this->assertEquals('sync', config('queue.default'));
    }

    public function test_session_driver_is_array(): void
    {
        $this->requiresPdoSqlite();
        $this->assertEquals('array', config('session.driver'));
    }

    public function test_mail_driver_is_array(): void
    {
        $this->requiresPdoSqlite();
        $this->assertEquals('array', config('mail.default'));
    }

    public function test_bcrypt_rounds_is_low(): void
    {
        $this->requiresPdoSqlite();
        $this->assertEquals(4, config('hashing.bcrypt.rounds'));
    }

    public function test_config_values_match_phpunit_xml(): void
    {
        $this->requiresPdoSqlite();
        $this->assertEquals('testing', env('APP_ENV'));
        $this->assertEquals('sqlite', env('DB_CONNECTION'));
        $this->assertEquals(':memory:', env('DB_DATABASE'));
        $this->assertEquals('array', env('CACHE_DRIVER'));
        $this->assertEquals('sync', env('QUEUE_CONNECTION'));
        $this->assertEquals('array', env('SESSION_DRIVER'));
        $this->assertEquals('array', env('MAIL_MAILER'));
    }

    public function test_route_middleware_has_throttle(): void
    {
        $this->requiresPdoSqlite();
        $routes = app('router')->getRoutes()->getRoutesByName();
        $home = $routes['home'] ?? null;
        $this->assertNotNull($home);
        $middleware = $home->gatherMiddleware();
        $this->assertContains('throttle:global', $middleware);
    }

    public function test_throttle_limiters_registered(): void
    {
        $this->requiresPdoSqlite();
        $this->assertNotNull(
            \Illuminate\Support\Facades\RateLimiter::limiter('global')
        );
        $this->assertNotNull(
            \Illuminate\Support\Facades\RateLimiter::limiter('sitemap')
        );
        $this->assertNotNull(
            \Illuminate\Support\Facades\RateLimiter::limiter('pdf')
        );
    }

    public function test_app_is_not_production(): void
    {
        $this->requiresPdoSqlite();
        $this->assertNotEquals('production', app()->environment());
    }

    public function test_pdo_sqlite_extension_loaded(): void
    {
        $this->requiresPdoSqlite();
        $this->assertTrue(
            extension_loaded('pdo_sqlite'),
            'Tests require pdo_sqlite for in-memory database'
        );
    }

    public function test_bootstrap_blocks_without_pdo_sqlite(): void
    {
        $this->requiresPdoSqlite();
        $connection = config('database.default');
        if ($connection === 'sqlite') {
            $this->assertTrue(
                extension_loaded('pdo_sqlite'),
                'bootstrap.php should abort if pdo_sqlite missing with sqlite connection'
            );
        }
    }

    public function test_testcase_fails_on_production_db(): void
    {
        $this->requiresPdoSqlite();
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $isProduction = in_array($connection, ['mysql', 'mysql2'])
            && in_array($database, ['faristol', 'web2']);
        $this->assertFalse($isProduction, 'TestCase must fail() if production DB detected');
    }
}
