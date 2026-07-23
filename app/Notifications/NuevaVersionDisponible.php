<?php

namespace App\Notifications;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevaVersionDisponible extends Notification
{
    use Queueable;

    public function __construct(
        protected Trabajo $trabajo,
        protected string $version
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $titulo = $this->trabajo->titulo;
        $version = strtoupper($this->version);

        $rol = $notifiable->rol ?? null;
        if ($rol === 'Administrador') {
            $url = route('admin.detallesTrabajo', $this->trabajo->id_trabajo);
        } else {
            $url = route('evaluador.dashboard');
        }

        return [
            'tipo'    => 'nueva_version',
            'titulo'  => 'Nueva versión disponible para revisar',
            'mensaje' => "El gestor subió la {$version} corregida del trabajo \"{$titulo}\". Ya puedes iniciar la nueva revisión.",
            'url'     => $url,
            'trabajo_id' => $this->trabajo->id_trabajo,
        ];
    }
}
