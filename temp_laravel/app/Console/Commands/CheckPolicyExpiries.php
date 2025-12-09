<?php

namespace App\Console\Commands;

use App\Services\PolicyExpiryService;
use Illuminate\Console\Command;

class CheckPolicyExpiries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'policies:check-expiries {--cleanup : Run cleanup of old notifications only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for policies and endorsements that are expiring and create notifications';

    /**
     * Execute the console command.
     */
    public function handle(PolicyExpiryService $policyExpiryService)
    {
        $this->info('Starting policy expiry check...');
        
        $startTime = microtime(true);

        if ($this->option('cleanup')) {
            $this->info('Cleaning up old notifications...');
            $policyExpiryService->cleanupOldNotifications();
            $this->info('Old notifications cleaned up successfully.');
        } else {
            $this->info('Checking for policy expiries...');
            $policyExpiryService->checkPolicyExpiries();
            $this->info('Policy expiry check completed.');

            $this->info('Checking for endorsement alerts...');
            $policyExpiryService->checkEndorsementAlerts();
            $this->info('Endorsement alerts check completed.');

            $this->info('Cleaning up old notifications...');
            $policyExpiryService->cleanupOldNotifications();
            $this->info('Old notifications cleaned up successfully.');
        }

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        // Display summary statistics
        $stats = $policyExpiryService->getNotificationStats();
        
        $this->info('');
        $this->info('=== NOTIFICATION SUMMARY ===');
        $this->info("Total Notifications: {$stats['total_notifications']}");
        $this->info("Unread Notifications: {$stats['unread_notifications']}");
        $this->info("Critical Priority: {$stats['critical_notifications']}");
        $this->info("High Priority: {$stats['high_notifications']}");
        $this->info("Medium Priority: {$stats['medium_notifications']}");
        $this->info("Low Priority: {$stats['low_notifications']}");
        $this->info("Policy Expiry Warnings: {$stats['policy_expiry_warnings']}");
        $this->info("Endorsement Alerts: {$stats['endorsement_alerts']}");
        $this->info("Execution Time: {$executionTime} seconds");

        return Command::SUCCESS;
    }
}