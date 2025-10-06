<?php

namespace App\Mail;

use App\Models\Profesional;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudRechazadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $profesional;
    public $motivoRechazo;

    /**
     * Create a new message instance.
     */
    public function __construct(Profesional $profesional, string $motivoRechazo)
    {
        $this->profesional = $profesional;
        $this->motivoRechazo = $motivoRechazo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 Actualización sobre tu solicitud - PsyConnect',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-rechazada',
            with: [
                'profesional' => $this->profesional,
                'motivoRechazo' => $this->motivoRechazo,
            ],
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