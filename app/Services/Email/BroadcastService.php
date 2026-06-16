<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use App\Models\User;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Log;

class BroadcastService
{
    /**
     * Send campaign broadcast to users based on the selected segment.
     */
    public function sendCampaign(EmailCampaign $campaign): void
    {
        $users = $this->getSegmentUsers($campaign->segment);

        foreach ($users as $user) {
            // This should ideally be queued individually
            try {
                Resend::emails()->send([
                    'from' => config('mail.from.address', 'epoxyndo@resend.dev'),
                    'to' => $user->email,
                    'subject' => $campaign->subject,
                    'html' => $campaign->body_html,
                ]);

                // Log email success/failure internally
            } catch (\Exception $e) {
                Log::error("Failed sending campaign #{$campaign->id} to {$user->email}: " . $e->getMessage());
            }
        }

        $campaign->update(['sent_at' => now(), 'total_recipients' => $users->count()]);
    }

    /**
     * Filter users by campaign segment.
     */
    protected function getSegmentUsers(string $segment)
    {
        switch ($segment) {
            case 'subscribed':
                return User::where('email_subscribed', true)->get();
            case 'vip':
                // Custom logic for high-spending users
                return User::whereHas('orders', function ($query) {
                    $query->selectRaw('user_id, SUM(total) as total_spend')
                        ->groupBy('user_id')
                        ->having('total_spend', '>', 5000000); // Spend > 5jt
                })->get();
            case 'inactive':
                // Customer who haven't ordered in the last 30 days
                return User::where(function ($query) {
                    $query->where('last_order_at', '<', now()->subDays(30))
                        ->orWhereNull('last_order_at');
                })->get();
            case 'all':
            default:
                return User::all();
        }
    }
}
