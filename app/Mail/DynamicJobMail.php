<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use App\Models\MailSetting;

class DynamicJobMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $email,
        public string $companyName,
        public string $positionName,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $settings = MailSetting::first();
        $subject = $settings ? $settings->subject : "Application for {position} at {company}";

        $parsedSubject = str_replace(
            ['{email}', '{company}', '{position}'],
            [$this->email, $this->companyName, $this->positionName],
            $subject
        );

        return new Envelope(
            subject: $parsedSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $settings = MailSetting::first();
        $body = $settings ? $settings->body : "Hello! I am applying for {position} at {company}.";

        $parsedBody = str_replace(
            ['{email}', '{company}', '{position}'],
            [$this->email, $this->companyName, $this->positionName],
            $body
        );

        return new Content(
            view: 'emails.job_application',
            with: [
                'customBody' => nl2br($parsedBody),
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
        $settings = MailSetting::first();
        if ($settings && $settings->attachment_path && Storage::exists($settings->attachment_path)) {
            $filename = (config('app.name') ?: 'My') . '_RESUME.pdf';
            return [
                Attachment::fromPath(Storage::path($settings->attachment_path))
                    ->as($filename)
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
