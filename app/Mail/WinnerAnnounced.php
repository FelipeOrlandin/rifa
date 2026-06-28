<?php

namespace App\Mail;

use App\Models\Winner;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WinnerAnnounced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Winner $winner,
        public User $user,
        public bool $isWinner,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isWinner 
            ? 'Parabéns! Você ganhou a rifa!'
            : 'Resultado do sorteio - Rifa Online';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.winner-announced',
            with: [
                'userName' => $this->user->name,
                'rifaTitle' => $this->winner->rifa->title ?? 'Rifa',
                'winningNumber' => $this->winner->winning_number,
                'isWinner' => $this->isWinner,
                'prizeValue' => $this->winner->rifa->prize_value,
                'drawDate' => $this->winner->sorteado_em?->format('d/m/Y H:i'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
