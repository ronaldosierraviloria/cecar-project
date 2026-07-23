<?php

namespace Tests\Unit;

use App\Models\Trabajo;
use App\Models\Usuario;
use App\Notifications\RetroalimentacionEmitida;
use Tests\TestCase;

class RetroalimentacionEmitidaTest extends TestCase
{
    public function test_to_database_contains_expected_payload(): void
    {
        $trabajo = new Trabajo();
        $trabajo->id_trabajo = 7;
        $trabajo->titulo = 'Proyecto Demo';

        $usuario = new Usuario();
        $usuario->nombre = 'Camila';
        $usuario->apellido = 'Ruiz';

        $notification = new RetroalimentacionEmitida($trabajo, $usuario);

        $payload = $notification->toDatabase(new Usuario());

        $this->assertSame('retroalimentacion', $payload['tipo']);
        $this->assertSame('Retroalimentación emitida', $payload['titulo']);
        $this->assertStringContainsString('Camila Ruiz', $payload['mensaje']);
        $this->assertSame(route('admin.detallesTrabajo', 7), $payload['url']);
    }
}
