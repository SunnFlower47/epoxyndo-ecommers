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
            from: new Address($marketingEmail, $companyName),
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign',
            with: [
                'body' => $this->campaign->body,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
