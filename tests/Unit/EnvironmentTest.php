<?php

namespace Tests\Unit;

use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    public function test_env_testing_file_exists(): void
    {
        $this->assertFileExists(base_path('.env.testing'));
    }

    public function test_environment_is_testing(): void
    {
        $this->assertEquals('testing', app()->environment());
    }

    public function test_db_connection_env_is_sqlite(): void
    {
        $this->assertEquals('sqlite', env('DB_CONNECTION'));
    }

    public function test_database_default_config_is_sqlite(): void
    {
        $this->assertEquals('sqlite', config('database.default'));
    }
}
