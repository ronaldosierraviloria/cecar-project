<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PropuestaEvaluada extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->trabajo->titulo;

        return [
            'tipo'    => 'propuesta_evaluada',
            'titulo'  => 'Propuesta evaluada',
            'mensaje' => "Todos los evaluadores finalizaron la evaluación de la propuesta \"{$titulo}\". Ya puedes subir el informe final.",
            'url'     => route('gestor.trabajo.detalles', $this->trabajo->id_trabajo),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
