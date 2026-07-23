<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TrabajoEliminadoEvaluador extends Notification
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
            'titulo'  => 'Proyecto eliminado permanentemente',
            'mensaje' => "El proyecto \"{$titulo}\" ha sido eliminado permanentemente por {$this->nombreActor}.",
            'url'     => route('evaluador.dashboard'),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
