<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GivingStatementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $emailData;
    public string $filePath;
    public string $fileName;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData, string $filePath, string $fileName)
    {
        $this->emailData = $emailData;
        $this->filePath = $filePath;
        $this->fileName = $fileName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->parseTemplate($this->emailData['subject']),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'giving_statements.emails.statement',
            with: [
                'data' => $this->parseBody(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->fileName)
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Parse template variables in subject.
     */
    private function parseTemplate(string $template): string
    {
        $replacements = [
            '{{church_name}}' => $this->emailData['church_name'],
            '{{member_name}}' => $this->emailData['member_name'],
            '{{period_from}}' => $this->emailData['period_from'],
            '{{period_to}}' => $this->emailData['period_to'],
            '{{total_amount}}' => number_format($this->emailData['total_amount'], 2),
            '{{transaction_count}}' => $this->emailData['transaction_count'],
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Parse body template.
     */
    private function parseBody(): array
    {
        $body = $this->emailData['body'];
        
        $replacements = [
            '{{church_name}}' => $this->emailData['church_name'],
            '{{member_name}}' => $this->emailData['member_name'],
            '{{period_from}}' => $this->emailData['period_from'],
            '{{period_to}}' => $this->emailData['period_to'],
            '{{total_amount}}' => number_format($this->emailData['total_amount'], 2),
            '{{transaction_count}}' => $this->emailData['transaction_count'],
            '{{password_hint}}' => $this->emailData['password_hint'] ? "Password Hint: {$this->emailData['password_hint']}" : '',
            '{{custom_message}}' => $this->emailData['custom_message'] ?? '',
        ];

        $parsedBody = str_replace(array_keys($replacements), array_values($replacements), $body);
        
        // Clean up empty lines
        $parsedBody = preg_replace('/\n{3,}/', "\n\n", $parsedBody);

        return [
            'church_name' => $this->emailData['church_name'],
            'member_name' => $this->emailData['member_name'],
            'period_from' => $this->emailData['period_from'],
            'period_to' => $this->emailData['period_to'],
            'total_amount' => number_format($this->emailData['total_amount'], 2),
            'transaction_count' => $this->emailData['transaction_count'],
            'password_hint' => $this->emailData['password_hint'],
            'body' => nl2br(e($parsedBody)),
        ];
    }
}
