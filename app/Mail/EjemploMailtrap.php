<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EjemploMailtrap extends Mailable
{
    use Queueable, SerializesModels;

    public string $destinatario;
    public string $mensajePersonalizado;

    /**
     * Create a new message instance.
     */
    public function __construct(string $destinatario, string $mensajePersonalizado)
    {
        $this->destinatario = $destinatario;
        $this->mensajePersonalizado = $mensajePersonalizado;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Prueba de Envío — Mailtrap',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ejemplo_mailtrap',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
