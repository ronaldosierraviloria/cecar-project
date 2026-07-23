<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PlazoExtendido extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo,
        protected Carbon $nuevaFecha
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->trabajo->titulo;
        $fechaFormateada = $this->nuevaFecha->format('d/m/Y');

        return [
            'tipo'    => 'plazo',
            'titulo'  => 'Tu plazo de revisión fue extendido',
            'mensaje' => "El administrador extendió tu plazo para evaluar \"{$titulo}\". Nueva fecha límite: {$fechaFormateada}.",
            'url'     => route('evaluador.dashboard'),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
