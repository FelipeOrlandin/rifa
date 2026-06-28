<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public User $user,
        public array $numbers,
        public float $totalAmount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pagamento Confirmado - Rifa Online',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-confirmed',
            with: [
                'userName' => $this->user->name,
                'rifaTitle' => $this->payment->rifa->title ?? 'Rifa',
                'numbers' => $this->numbers,
                'totalAmount' => $this->totalAmount,
                'paymentDate' => $this->payment->created_at->format('d/m/Y H:i'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
