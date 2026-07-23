<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EvaluadorAsignado extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo,
        protected Carbon $fechaLimite
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->trabajo->titulo;
        $fechaFormateada = $this->fechaLimite->format('d/m/Y');

        return [
            'tipo'    => 'asignacion',
            'titulo'  => 'Fuiste asignado como evaluador',
            'mensaje' => "Has sido asignado para evaluar: \"{$titulo}\". Fecha límite: {$fechaFormateada}.",
            'url'     => route('evaluador.dashboard'),
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
