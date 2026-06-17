<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public Campaign $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function envelope(): Envelope
    {
        $settings = app(GeneralSettings::class);
        $marketingEmail = $settings->marketing_email ?? 'marketing@epoxyndo.com';
        $companyName = $settings->company_name ?? 'Promo Epoxyndo';

        return new Envelope(
            from: new Address(config('mail.from.address'), $companyName),
            replyTo: [
                new Address($marketingEmail, $companyName),
            ],
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        $body = $this->campaign->body;
        $baseUrl = rtrim(config('app.url'), '/');

        // Convert src="/storage/" or href="/storage/" to absolute URLs
        $body = preg_replace('/(src|href)=["\']\/storage\//i', '$1="' . $baseUrl . '/storage/', $body);
        // Convert src="storage/" or href="storage/" to absolute URLs
        $body = preg_replace('/(src|href)=["\']storage\//i', '$1="' . $baseUrl . '/storage/', $body);

        return new Content(
            view: 'emails.campaign',
            with: [
                'body' => $body,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
