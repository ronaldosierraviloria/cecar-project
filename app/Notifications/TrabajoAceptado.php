<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TrabajoAceptado extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo,
        protected string $nombreEvaluador,
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
            'tipo'    => 'trabajo_aceptado',
            'titulo'  => 'Trabajo aceptado por evaluador',
            'mensaje' => "El evaluador {$this->nombreEvaluador} aceptó el trabajo \"{$titulo}\" para su evaluación.",
            'url'     => $url,
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
