<?php

namespace Tests\Unit\Mail;

use App\Mail\EjemploMailtrap;
use App\Mail\TrabajoSubidoEstudiante;
use App\Models\Trabajo;
use Tests\TestCase;

class MailTest extends TestCase
{
    public function test_ejemplo_mailtrap_envelope(): void
    {
        $mail = new EjemploMailtrap('Juan Pérez', 'Mensaje de prueba');
        $envelope = $mail->envelope();

        $this->assertSame('Prueba de Envío — Mailtrap', $envelope->subject);
    }

    public function test_ejemplo_mailtrap_content(): void
    {
        $mail = new EjemploMailtrap('Juan Pérez', 'Mensaje de prueba');
        $content = $mail->content();

        $this->assertSame('emails.ejemplo_mailtrap', $content->view);
    }

    public function test_ejemplo_mailtrap_public_properties(): void
    {
        $mail = new EjemploMailtrap('Ana López', 'Test message');
        $this->assertSame('Ana López', $mail->destinatario);
        $this->assertSame('Test message', $mail->mensajePersonalizado);
    }

    public function test_ejemplo_mailtrap_no_attachments(): void
    {
        $mail = new EjemploMailtrap('Test', 'Test');
        $this->assertEmpty($mail->attachments());
    }

    public function test_trabajo_subido_estudiante_envelope(): void
    {
        $trabajo = new Trabajo();
        $trabajo->id_trabajo = 1;
        $trabajo->titulo = 'Proyecto';

        $mail = new TrabajoSubidoEstudiante($trabajo, 'Carlos Ruiz');
        $envelope = $mail->envelope();

        $this->assertSame('Tu trabajo de grado ha sido subido', $envelope->subject);
    }

    public function test_trabajo_subido_estudiante_content(): void
    {
        $trabajo = new Trabajo();
        $trabajo->id_trabajo = 1;
        $trabajo->titulo = 'Proyecto';

        $mail = new TrabajoSubidoEstudiante($trabajo, 'Carlos Ruiz');
        $content = $mail->content();

        $this->assertSame('emails.trabajo_subido_estudiante', $content->view);
    }

    public function test_trabajo_subido_estudiante_public_properties(): void
    {
        $trabajo = new Trabajo();
        $trabajo->id_trabajo = 1;
        $trabajo->titulo = 'IA Aplicada';

        $mail = new TrabajoSubidoEstudiante($trabajo, 'Luis Pérez');
        $this->assertSame('Luis Pérez', $mail->nombreEstudiante);
        $this->assertSame('IA Aplicada', $mail->trabajo->titulo);
    }
}
