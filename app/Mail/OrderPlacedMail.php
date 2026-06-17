<?php

namespace App\Mail;

use App\Models\Order;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {}

    public function envelope(): Envelope
    {
        $settings = app(GeneralSettings::class);
        $supportEmail = $settings->support_email ?? 'noreply@epoxyndo.com';
        $companyName = $settings->company_name ?? 'Sistem Epoxyndo';

        return new Envelope(
            from: new Address($supportEmail, $companyName),
            subject: 'Pesanan Diterima - ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.placed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
