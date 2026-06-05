<?php

namespace App\Console\Commands;

use App\Http\Controllers\SubscriptionController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:bill';

    /**
     * The console command description.
     */
    protected $description = 'Process subscription billing for due dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('[' . now()->toDateTimeString() . '] Starting subscription billing process...');
        Log::info('[ProcessSubscriptions] Command started.');

        try {
            $results = SubscriptionController::processMonthlyBilling();

            $this->info('Billing complete:');
            $this->table(
                ['Status', 'Count'],
                [
                    ['Processed (invoices created)', $results['processed']],
                    ['Skipped (already billed)',     $results['skipped']],
                    ['Errors',                       $results['errors']],
                ]
            );

            if ($results['errors'] > 0) {
                $this->warn('Some subscriptions had errors. Check the application log for details.');
                Log::warning('[ProcessSubscriptions] Completed with errors.', $results);
            } else {
                Log::info('[ProcessSubscriptions] Completed successfully.', $results);
            }

            return $results['errors'] > 0 ? self::FAILURE : self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Fatal error during subscription billing: ' . $e->getMessage());
            Log::error('[ProcessSubscriptions] Fatal error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
