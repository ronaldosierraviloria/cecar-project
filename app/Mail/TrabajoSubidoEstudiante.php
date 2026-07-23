<?php

namespace App\Mail;

use App\Models\Trabajo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrabajoSubidoEstudiante extends Mailable
{
    use Queueable, SerializesModels;

    public Trabajo $trabajo;
    public string $nombreEstudiante;

    public function __construct(Trabajo $trabajo, string $nombreEstudiante)
    {
        $this->trabajo = $trabajo;
        $this->nombreEstudiante = $nombreEstudiante;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu trabajo de grado ha sido subido',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trabajo_subido_estudiante',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
