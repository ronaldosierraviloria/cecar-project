<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InformeFinalSubido extends Notification
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
            'tipo'    => 'informe_final',
            'titulo'  => 'Informe final subido',
            'mensaje' => "El informe final de \"{$titulo}\" ha sido subido. El trabajo ahora es un Trabajo de Grado y está listo para ser evaluado.",
            'url'     => route('evaluador.dashboard'),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
