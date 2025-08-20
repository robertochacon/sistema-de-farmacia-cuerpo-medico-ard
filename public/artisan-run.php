<?php

use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Obtener comando desde la URL
$command = $_GET['cmd'] ?? null;
if (!$command) {
    die("❌ No se especificó comando. Usa ?cmd=migrate:fresh --seed");
}

// Ejecutar el comando
$kernel->call($command);

// Mostrar resultado
echo "<pre>" . Artisan::output() . "</pre>";
