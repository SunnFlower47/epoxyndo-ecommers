<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry job up to 3 times if fails
    public $tries = 3;

    public function __construct(public Campaign $campaign, public Subscriber $subscriber)
    {}

    public function handle(): void
    {
        Mail::to($this->subscriber->email)->send(new CampaignMail($this->campaign));
        
        $this->campaign->increment('sent_count');
    }
}
