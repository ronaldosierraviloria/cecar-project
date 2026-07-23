<?php

namespace Tests\Unit\Notifications;

use App\Models\Trabajo;
use App\Models\Usuario;
use App\Notifications\EvaluadorAsignado;
use App\Notifications\InformeFinalSubido;
use App\Notifications\NuevoTrabajoSubido;
use App\Notifications\NuevaVersionDisponible;
use App\Notifications\PlazoExtendido;
use App\Notifications\PropuestaEvaluada;
use App\Notifications\RetroalimentacionEmitida;
use App\Notifications\RetroalimentacionFinalizada;
use App\Notifications\TrabajoAprobado;
use App\Notifications\TrabajoEliminado;
use App\Notifications\TrabajoEliminadoEvaluador;
use App\Notifications\TrabajoReactivado;
use App\Notifications\TrabajoRetirado;
use App\Notifications\TrabajoRetiradoEvaluador;
use Carbon\Carbon;
use Tests\TestCase;

class AllNotificationsTest extends TestCase
{
    private Trabajo $trabajo;
    private Usuario $evaluador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trabajo = new Trabajo();
        $this->trabajo->id_trabajo = 99;
        $this->trabajo->titulo = 'Sistema de Gestión';

        $this->evaluador = new Usuario();
        $this->evaluador->id_usuario = 1;
        $this->evaluador->nombre = 'Camila';
        $this->evaluador->apellido = 'Ruiz';
        $this->evaluador->rol = 'Evaluador';
    }

    public function test_evaluador_asignado_to_database(): void
    {
        $fechaLimite = Carbon::parse('2026-08-15');
        $notification = new EvaluadorAsignado($this->trabajo, $fechaLimite);
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('asignacion', $payload['tipo']);
        $this->assertSame('Fuiste asignado como evaluador', $payload['titulo']);
        $this->assertStringContainsString('Sistema de Gestión', $payload['mensaje']);
        $this->assertStringContainsString('15/08/2026', $payload['mensaje']);
        $this->assertStringContainsString('/evaluador', $payload['url']);
    }

    public function test_nuevo_trabajo_subido_to_database(): void
    {
        $notification = new NuevoTrabajoSubido($this->trabajo, 'Carlos Gestor');
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('nuevo_trabajo', $payload['tipo']);
        $this->assertSame('Nuevo trabajo de grado subido', $payload['titulo']);
        $this->assertStringContainsString('Carlos Gestor', $payload['mensaje']);
        $this->assertStringContainsString('Sistema de Gestión', $payload['mensaje']);
    }

    public function test_retroalimentacion_emitida_to_database(): void
    {
        $notification = new RetroalimentacionEmitida($this->trabajo, $this->evaluador);
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('retroalimentacion', $payload['tipo']);
        $this->assertSame('Retroalimentación emitida', $payload['titulo']);
        $this->assertStringContainsString('Camila Ruiz', $payload['mensaje']);
        $this->assertStringContainsString('/admin/proyecto/', $payload['url']);
    }

    public function test_retroalimentacion_finalizada_to_database(): void
    {
        $notification = new RetroalimentacionFinalizada($this->trabajo);
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('retroalimentacion', $payload['tipo']);
        $this->assertSame('Retroalimentación finalizada', $payload['titulo']);
        $this->assertStringContainsString('Ambos jurados', $payload['mensaje']);
    }

    public function test_nueva_version_disponible_to_database(): void
    {
        $notification = new NuevaVersionDisponible($this->trabajo, 'v2');
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('nueva_version', $payload['tipo']);
        $this->assertSame('Nueva versión disponible para revisar', $payload['titulo']);
        $this->assertStringContainsString('V2', $payload['mensaje']);
        $this->assertStringContainsString('/evaluador', $payload['url']);
    }

    public function test_nueva_version_disponible_admin_url(): void
    {
        $admin = new Usuario();
        $admin->rol = 'Administrador';

        $notification = new NuevaVersionDisponible($this->trabajo, 'v2');
        $payload = $notification->toDatabase($admin);

        $this->assertStringContainsString('/admin/proyecto/', $payload['url']);
    }

    public function test_trabajo_aprobado_to_database(): void
    {
        $notification = new TrabajoAprobado($this->trabajo);
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('aprobado', $payload['tipo']);
        $this->assertSame('¡Trabajo de grado aprobado!', $payload['titulo']);
        $this->assertStringContainsString('aprobado oficialmente', $payload['mensaje']);
    }

    public function test_trabajo_retirado_to_database(): void
    {
        $notification = new TrabajoRetirado($this->trabajo, 'Admin User');
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('trabajo_retirado', $payload['tipo']);
        $this->assertSame('Trabajo de grado retirado', $payload['titulo']);
        $this->assertStringContainsString('Admin User', $payload['mensaje']);
    }

    public function test_trabajo_reactivado_to_database(): void
    {
        $notification = new TrabajoReactivado($this->trabajo, 'Admin User');
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('trabajo_reactivado', $payload['tipo']);
        $this->assertSame('Trabajo de grado reactivado', $payload['titulo']);
        $this->assertStringContainsString('Admin User', $payload['mensaje']);
    }

    public function test_trabajo_eliminado_to_database(): void
    {
        $notification = new TrabajoEliminado($this->trabajo, 'Admin User');
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('trabajo_eliminado', $payload['tipo']);
        $this->assertSame('Trabajo de grado eliminado', $payload['titulo']);
        $this->assertStringContainsString('Admin User', $payload['mensaje']);
    }

    public function test_trabajo_eliminado_evaluador_to_database(): void
    {
        $notification = new TrabajoEliminadoEvaluador($this->trabajo, 'Admin');
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('trabajo_eliminado', $payload['tipo']);
        $this->assertSame('Proyecto eliminado permanentemente', $payload['titulo']);
        $this->assertStringContainsString('eliminado permanentemente', $payload['mensaje']);
    }

    public function test_trabajo_retirado_evaluador_to_database(): void
    {
        $notification = new TrabajoRetiradoEvaluador($this->trabajo, 'Admin');
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('trabajo_retirado', $payload['tipo']);
        $this->assertSame('Proyecto retirado', $payload['titulo']);
        $this->assertStringContainsString('retirado por Admin', $payload['mensaje']);
    }

    public function test_propuesta_evaluada_to_database(): void
    {
        $notification = new PropuestaEvaluada($this->trabajo);
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('propuesta_evaluada', $payload['tipo']);
        $this->assertSame('Propuesta evaluada', $payload['titulo']);
        $this->assertStringContainsString('informe final', $payload['mensaje']);
    }

    public function test_plazo_extendido_to_database(): void
    {
        $nuevaFecha = Carbon::parse('2026-09-01');
        $notification = new PlazoExtendido($this->trabajo, $nuevaFecha);
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('plazo', $payload['tipo']);
        $this->assertSame('Tu plazo de revisión fue extendido', $payload['titulo']);
        $this->assertStringContainsString('01/09/2026', $payload['mensaje']);
    }

    public function test_informe_final_subido_to_database(): void
    {
        $notification = new InformeFinalSubido($this->trabajo);
        $payload = $notification->toDatabase($this->evaluador);

        $this->assertSame('informe_final', $payload['tipo']);
        $this->assertSame('Informe final subido', $payload['titulo']);
        $this->assertStringContainsString('Trabajo de Grado', $payload['mensaje']);
    }

    public function test_all_notifications_via_database_channel(): void
    {
        $notifications = [
            new EvaluadorAsignado($this->trabajo, Carbon::now()),
            new InformeFinalSubido($this->trabajo),
            new NuevoTrabajoSubido($this->trabajo, 'Gestor'),
            new NuevaVersionDisponible($this->trabajo, 'v2'),
            new PlazoExtendido($this->trabajo, Carbon::now()),
            new PropuestaEvaluada($this->trabajo),
            new RetroalimentacionEmitida($this->trabajo, $this->evaluador),
            new RetroalimentacionFinalizada($this->trabajo),
            new TrabajoAprobado($this->trabajo),
            new TrabajoEliminado($this->trabajo, 'Admin'),
            new TrabajoEliminadoEvaluador($this->trabajo, 'Admin'),
            new TrabajoReactivado($this->trabajo, 'Admin'),
            new TrabajoRetirado($this->trabajo, 'Admin'),
            new TrabajoRetiradoEvaluador($this->trabajo, 'Admin'),
        ];

        foreach ($notifications as $notification) {
            $channels = $notification->via($this->evaluador);
            $this->assertContains('database', $channels, get_class($notification) . ' should use database channel');
        }
    }
}
