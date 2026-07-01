<?php

namespace App\Mail;

use App\Models\DemandeInscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompteApprouve extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DemandeInscription $demande,
        public string $motDePasse,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'GPI — Votre compte a été approuvé');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.compte-approuve');
    }
}