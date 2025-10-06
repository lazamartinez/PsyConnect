<?php

namespace App\Mail;

use App\Models\Profesional;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudAprobadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $profesional;

    /**
     * Create a new message instance.
     */
    public function __construct(Profesional $profesional)
    {
        $this->profesional = $profesional;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 ¡Tu solicitud ha sido aprobada! - PsyConnect',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-aprobada',
            with: [
                'profesional' => $this->profesional,
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