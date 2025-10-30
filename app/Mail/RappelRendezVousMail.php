<?php

namespace App\Mail;

use App\Models\Rendezvous;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelRendezVousMail extends Mailable
{
    use Queueable, SerializesModels;

    public $rendezVous;

    public function __construct(Rendezvous $rendezVous)
    {
        $this->rendezVous = $rendezVous;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel de votre rendez-vous médical',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rappel-rendezvous',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
