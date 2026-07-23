<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use App\Models\Trabajo;
use App\Notifications\NuevoTrabajoSubido;
use App\Notifications\EvaluadorAsignado;
use Carbon\Carbon;

echo "=== Test Sistema de Notificaciones ===\n\n";

// 1. Verificar usuarios por rol
$admin  = Usuario::where('rol', 'Admin')->where('activo', true)->first();
$gestor = Usuario::where('rol', 'Gestor')->where('activo', true)->first();
$evaluador = Usuario::where('rol', 'Evaluador')->where('activo', true)->first();

echo "Admin encontrado:    " . ($admin    ? $admin->nombre . ' ' . $admin->apellido    : 'NINGUNO') . "\n";
echo "Gestor encontrado:   " . ($gestor   ? $gestor->nombre . ' ' . $gestor->apellido   : 'NINGUNO') . "\n";
echo "Evaluador encontrado:" . ($evaluador ? $evaluador->nombre . ' ' . $evaluador->apellido : 'NINGUNO') . "\n\n";

// 2. Verificar que hay trabajos
$trabajo = Trabajo::first();
echo "Trabajo encontrado:  " . ($trabajo ? $trabajo->titulo : 'NINGUNO') . "\n\n";

// 3. Enviar notificación de prueba al admin
if ($admin && $trabajo) {
    $antes = $admin->notifications()->count();
    $admin->notify(new NuevoTrabajoSubido($trabajo, 'Gestor de Prueba'));
    $despues = $admin->notifications()->count();
    echo "✅ NuevoTrabajoSubido enviado al Admin ({$antes} → {$despues} notificaciones)\n";

    // Verificar el contenido
    $ultima = $admin->notifications()->latest()->first();
    $data = json_decode($ultima->data, true);
    echo "   Tipo:    {$data['tipo']}\n";
    echo "   Título:  {$data['titulo']}\n";
    echo "   Mensaje: {$data['mensaje']}\n";
    echo "   URL:     {$data['url']}\n\n";
}

// 4. Enviar notificación al evaluador
if ($evaluador && $trabajo) {
    $fechaLimite = Carbon::now()->addDays(21);
    $antes = $evaluador->notifications()->count();
    $evaluador->notify(new EvaluadorAsignado($trabajo, $fechaLimite));
    $despues = $evaluador->notifications()->count();
    echo "✅ EvaluadorAsignado enviado al Evaluador ({$antes} → {$despues} notificaciones)\n";
}

echo "\n=== Test completado con éxito ===\n";
