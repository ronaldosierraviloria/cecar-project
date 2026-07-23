<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TrabajoRechazado extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo,
        protected string $nombreEvaluador,
        protected string $motivo,
        protected ?string $url = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->trabajo->titulo;
        $url = $this->url ?? route('admin.detallesTrabajo', $this->trabajo->id_trabajo);

        return [
            'tipo'    => 'trabajo_rechazado',
            'titulo'  => 'Trabajo rechazado por evaluador',
            'mensaje' => "El evaluador {$this->nombreEvaluador} rechazó el trabajo \"{$titulo}\". Motivo: {$this->motivo}. Se requiere asignar un nuevo evaluador.",
            'url'     => $url,
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
