<?php

namespace App\Events;

use App\Models\Retroalimentacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoComentarioPublicado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $retroalimentacion;

    /**
     * Create a new event instance.
     */
    public function __construct(Retroalimentacion $retroalimentacion)
    {
        // Eager load the user relation (usuario)
        $this->retroalimentacion = $retroalimentacion->load('usuario');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('trabajo.' . $this->retroalimentacion->trabajo_grado_id),
        ];
    }

    /**
     * Custom event name for Laravel Echo
     */
    public function broadcastAs(): string
    {
        return 'comentario.publicado';
    }
}
