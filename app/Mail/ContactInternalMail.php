<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ContactInternalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contact $contact) {}

    public function envelope(): Envelope
    {
        $subject = sprintf(
            '%s %s님의 문의가 등록되었습니다.',
            $this->contact->company,
            $this->contact->contact_person
        );

        return new Envelope(
            subject: $subject,
            replyTo: [
                new Address($this->contact->email, $this->contact->contact_person),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.internal',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $out = [];
        foreach ($this->contact->attachments ?? [] as $row) {
            $path = $row['path'] ?? '';
            if ($path === '' || ! Storage::disk('public')->exists($path)) {
                continue;
            }
            $out[] = Attachment::fromStorageDisk('public', $path)
                ->as($row['original_name'] ?? basename($path));
        }

        return $out;
    }
}
