<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $nama;
    /**
     * Create a new message instance.
     */

    public function __construct($nama)
    {
        //
        $this->nama = $nama;
    }

    /**
     * Get the message envelope.
     */
    public function build()
    {
        return $this->subject('Notifikasi: Activasi Register Email ' .$this->nama)
                    ->markdown('emails.email');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Activasi Register Email Politeknik Kampar ' .$this->nama,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
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
