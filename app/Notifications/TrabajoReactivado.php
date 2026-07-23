<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TrabajoReactivado extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo,
        protected string $nombreActor
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->trabajo->titulo;

        return [
            'tipo'    => 'trabajo_reactivado',
            'titulo'  => 'Trabajo de grado reactivado',
            'mensaje' => "{$this->nombreActor} reactivó el trabajo: \"{$titulo}\".",
            'url'     => route('admin.detallesTrabajo', $this->trabajo->id_trabajo),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
