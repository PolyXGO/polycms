<?php

namespace App\Mail;

use App\Services\EmailTemplateManager;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DynamicTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectText;
    protected string $bodyText;

    /**
     * Create a new message instance.
     */
    public function __construct(string $templateKey, array $data = [])
    {
        /** @var EmailTemplateManager $manager */
        $manager = app(EmailTemplateManager::class);
        $rendered = $manager->render($templateKey, $data);

        $this->subjectText = $rendered['subject'];
        $this->bodyText = $rendered['body'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->bodyText,
        );
    }
}
