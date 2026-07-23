<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;

$roles = Usuario::select('rol')->distinct()->get()->pluck('rol');
echo "Roles en BD:\n";
foreach ($roles as $rol) {
    $count = Usuario::where('rol', $rol)->count();
    echo "  [{$rol}] → {$count} usuario(s)\n";
}
