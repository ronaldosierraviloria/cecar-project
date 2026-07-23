<?php

namespace App\Notifications;

use App\Models\Trabajo;
use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RetroalimentacionEmitida extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo,
        protected Usuario $evaluador
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->trabajo->titulo;
        $nombreEvaluador = trim(($this->evaluador->nombre ?? '') . ' ' . ($this->evaluador->apellido ?? ''));

        return [
            'tipo' => 'retroalimentacion',
            'titulo' => 'Retroalimentación emitida',
            'mensaje' => "{$nombreEvaluador} emitió retroalimentación para el trabajo \"{$titulo}\". El gestor puede revisar el avance.",
            'url' => route('admin.detallesTrabajo', $this->trabajo->id_trabajo),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
