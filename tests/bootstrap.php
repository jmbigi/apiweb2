<?php

require __DIR__ . '/../vendor/autoload.php';

$appEnv = getenv('APP_ENV');
if ($appEnv !== 'testing') {
    die("\n🔴 ERROR: APP_ENV=$appEnv (debe ser 'testing').\n"
      . "   Ejecuta: php artisan config:clear\n\n");
}

if (!file_exists(__DIR__ . '/../.env.testing')) {
    die("\n🔴 ERROR: No existe el archivo .env.testing.\n"
      . "   Créalo para evitar cargar config de producción.\n\n");
}

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$connection = $app['config']['database.default'];
$database = $app['config']["database.connections.{$connection}.database"];
if (in_array($connection, ['mysql', 'mysql2']) && in_array($database, ['faristol', 'web2'])) {
    die("\n🔴 ERROR: Tests apuntan a BD producción ($database).\n"
      . "   Usa .env.testing, SQLite, o una BD de prueba dedicada.\n\n");
}

if ($app->configurationIsCached()) {
    die("\n🔴 ERROR: Config cacheado detectado. phpunit.xml NO se respeta.\n"
      . "   Ejecuta: php artisan config:clear\n\n");
}
