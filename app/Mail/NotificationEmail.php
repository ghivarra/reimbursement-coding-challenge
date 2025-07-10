<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $ownerName; 
    protected string $reimbursementName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $ownerName, string $reimbursementName)
    {
        $this->ownerName = $ownerName;
        $this->reimbursementName = $reimbursementName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('custom.email'), 'Ghivarra Senandika R'),
            subject: 'Pengajuan Reimbursement Baru Masuk!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            with: [
                'ownerName'         => $this->ownerName,
                'reimbursementName' => $this->reimbursementName,
            ]
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
