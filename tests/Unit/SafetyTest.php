<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SafetyTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectRoot = dirname(__DIR__, 2);
    }

    public function test_env_file_is_gitignored(): void
    {
        $gitignore = file_get_contents($this->projectRoot . '/.gitignore');
        $this->assertStringContainsString('.env', $gitignore);
    }

    public function test_cache_directory_is_gitignored(): void
    {
        $gitignore = file_get_contents($this->projectRoot . '/.gitignore');
        $this->assertStringContainsString('public/cache/', $gitignore);
    }

    public function test_web_directory_is_gitignored(): void
    {
        $gitignore = file_get_contents($this->projectRoot . '/.gitignore');
        $this->assertStringContainsString('public/web/', $gitignore);
    }

    public function test_env_testing_file_exists(): void
    {
        $this->assertFileExists($this->projectRoot . '/.env.testing');
    }

    public function test_phpunit_xml_has_sqlite_connection(): void
    {
        $xml = file_get_contents($this->projectRoot . '/phpunit.xml');
        $this->assertStringContainsString(
            '<env name="DB_CONNECTION" value="sqlite"/>',
            $xml
        );
        $this->assertStringContainsString(
            '<env name="DB_DATABASE" value=":memory:"/>',
            $xml
        );
    }

    public function test_phpunit_xml_env_values_not_commented(): void
    {
        $xml = file_get_contents($this->projectRoot . '/phpunit.xml');
        $this->assertStringNotContainsString(
            '<!-- <env name="DB_CONNECTION"',
            $xml,
            'DB_CONNECTION env must NOT be commented out in phpunit.xml'
        );
    }

    public function test_env_testing_has_sqlite_config(): void
    {
        $env = file_get_contents($this->projectRoot . '/.env.testing');
        $this->assertStringContainsString('DB_CONNECTION=sqlite', $env);
        $this->assertStringContainsString('DB_DATABASE=:memory:', $env);
    }

    public function test_agents_md_has_test_rules(): void
    {
        $agents = file_get_contents($this->projectRoot . '/AGENTS.MD');
        $this->assertStringContainsString(
            'DB_CONNECTION=sqlite',
            $agents,
            'AGENTS.MD must document that tests use sqlite in memory'
        );
        $this->assertStringContainsString(
            'pdo_sqlite',
            $agents,
            'AGENTS.MD must document pdo_sqlite requirement'
        );
    }

    public function test_checklist_md_exists(): void
    {
        $this->assertFileExists($this->projectRoot . '/CHECKLIST.md');
    }

    public function test_clear_pdf_cache_command_exists(): void
    {
        $this->assertFileExists(
            $this->projectRoot . '/app/Console/Commands/ClearPdfCache.php'
        );
    }

    public function test_backend_dev_server_exists(): void
    {
        $this->assertFileExists($this->projectRoot . '/backend-dev-server.sh');
    }

    public function test_build_visorweb_script_exists(): void
    {
        $this->assertFileExists($this->projectRoot . '/build_visorweb2.sh');
    }

    public function test_public_backend_script_removed(): void
    {
        $this->assertFileDoesNotExist(
            $this->projectRoot . '/public/backend-laravel-run.sh'
        );
    }

    public function test_scripts_updated_for_web2(): void
    {
        $certbot = file_get_contents($this->projectRoot . '/certbot_testfile.sh');
        $this->assertStringContainsString('web2.faristol.net', $certbot);

        $traer = file_get_contents($this->projectRoot . '/traer_visor_web.sh');
        $this->assertStringContainsString('build_visorweb2.sh', $traer);
    }
}
