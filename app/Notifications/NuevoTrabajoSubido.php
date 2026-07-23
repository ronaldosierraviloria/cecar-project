<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevoTrabajoSubido extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo,
        protected string $nombreGestor
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->trabajo->titulo;

        return [
            'tipo'    => 'nuevo_trabajo',
            'titulo'  => 'Nuevo trabajo de grado subido',
            'mensaje' => "El gestor {$this->nombreGestor} subió el trabajo: \"{$titulo}\".",
            'url'     => route('admin.detallesTrabajo', $this->trabajo->id_trabajo),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
