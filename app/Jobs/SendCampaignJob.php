<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Campaign $campaign)
    {}

    public function handle(): void
    {
        // Chunk to avoid memory issues if there are many subscribers
        Subscriber::where('is_active', true)->chunk(100, function ($subscribers) {
            foreach ($subscribers as $subscriber) {
                SendCampaignEmailJob::dispatch($this->campaign, $subscriber);
            }
        });

        // Send Web Notifications to all registered users
        \App\Models\User::chunk(100, function ($users) {
            \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\WebPromoNotification($this->campaign));
        });

        $this->campaign->update([
            'status' => 'sent', // Marks the dispatch process as finished, actual emails are in queue
            'sent_at' => now(),
        ]);
    }
}
