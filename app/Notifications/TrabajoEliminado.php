<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TrabajoEliminado extends Notification
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
            'tipo'    => 'trabajo_eliminado',
            'titulo'  => 'Trabajo de grado eliminado',
            'mensaje' => "{$this->nombreActor} eliminó el trabajo: \"{$titulo}\".",
            'url'     => route('admin.dashboard'),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
