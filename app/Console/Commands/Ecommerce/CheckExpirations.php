<?php

namespace App\Console\Commands\Ecommerce;

use Illuminate\Console\Command;

class CheckExpirations extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'ecommerce:check-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for subscriptions and licenses expiring soon and send reminders.';


    /**
     * Execute the console command.
     */
    public function handle(\App\Services\Ecommerce\EmailManager $emailManager)
    {
        $this->info('Checking for expirations...');

        // Check for subscriptions expiring in 7, 3, and 1 day(s)
        $reminderDays = [7, 3, 1];

        foreach ($reminderDays as $days) {
            $expiryDate = \Carbon\Carbon::now()->addDays($days)->toShortDateString();
            
            $subscriptions = \App\Models\Ecommerce\Subscription::where('status', 'active')
                ->whereDate('expires_at', $expiryDate)
                ->get();

            foreach ($subscriptions as $subs) {
                $emailManager->sendRenewalReminderEmail($subs, $days);
                $this->line("Sent reminder for subscription #{$subs->id} ({$days} days left)");
            }
        }

        $this->info('Expiration check completed.');
    }
}
