<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite') {
            throw new \RuntimeException(
                'TESTS BLOQUEADOS: Los tests solo pueden ejecutarse con SQLite en memoria. '.
                'Driver detectado: '.DB::connection()->getDriverName()
            );
        }
    }
}
