<?php

namespace App\Services;

use App\Models\InsurancePolicy;
use App\Models\PolicyEndorsement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PolicyExpiryService
{
    /**
     * Check for policies expiring in the next few days and create notifications.
     */
    public function checkPolicyExpiries(): void
    {
        try {
            DB::beginTransaction();

            $today = Carbon::now();
            
            // Get all active policies that are expiring
            $expiringPolicies = InsurancePolicy::where('status', 'ACTIVE')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $today->copy()->addDays(30)) // Check next 30 days
                ->with(['creator', 'entities'])
                ->get();

            foreach ($expiringPolicies as $policy) {
                $daysUntilExpiry = $today->diffInDays($policy->end_date, false);
                
                // Skip if policy expired (negative days)
                if ($daysUntilExpiry < 0) {
                    continue;
                }

                // Create notifications based on expiry timeframe
                if ($daysUntilExpiry === 1) {
                    $this->createPolicyExpiryNotification($policy, 'CRITICAL', 'Policy Expiring Tomorrow', 
                        "Policy {$policy->policy_number} ({$policy->insurance_type}) expires tomorrow. Immediate action required.");
                } elseif ($daysUntilExpiry === 2) {
                    $this->createPolicyExpiryNotification($policy, 'HIGH', 'Policy Expiring in 2 Days', 
                        "Policy {$policy->policy_number} ({$policy->insurance_type}) expires in 2 days. Please renew soon.");
                } elseif ($daysUntilExpiry === 7) {
                    $this->createPolicyExpiryNotification($policy, 'MEDIUM', 'Policy Expiring in 1 Week', 
                        "Policy {$policy->policy_number} ({$policy->insurance_type}) expires in 1 week. Consider renewal planning.");
                } elseif ($daysUntilExpiry === 14) {
                    $this->createPolicyExpiryNotification($policy, 'LOW', 'Policy Expiring in 2 Weeks', 
                        "Policy {$policy->policy_number} ({$policy->insurance_type}) expires in 2 weeks. Renewal planning recommended.");
                } elseif ($daysUntilExpiry === 30) {
                    $this->createPolicyExpiryNotification($policy, 'LOW', 'Policy Expiring in 1 Month', 
                        "Policy {$policy->policy_number} ({$policy->insurance_type}) expires in 1 month. Start renewal planning.");
                }
            }

            // Check for already expired policies
            $expiredPolicies = InsurancePolicy::where('status', 'ACTIVE')
                ->whereDate('end_date', '<', $today)
                ->with(['creator', 'entities'])
                ->get();

            foreach ($expiredPolicies as $policy) {
                $daysExpired = $today->diffInDays($policy->end_date);
                
                // Create expiry notification if not already created today
                if (!$this->hasRecentNotification($policy->creator_id, 'POLICY_EXPIRED', $policy->id)) {
                    $this->createPolicyExpiryNotification($policy, 'CRITICAL', 'Policy Expired', 
                        "Policy {$policy->policy_number} ({$policy->insurance_type}) expired {$daysExpired} days ago. Immediate renewal required.");
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checking policy expiries: ' . $e->getMessage());
        }
    }

    /**
     * Check for endorsements that need attention and create notifications.
     */
    public function checkEndorsementAlerts(): void
    {
        try {
            DB::beginTransaction();

            $today = Carbon::now();
            
            // Check endorsements with future effective dates (pending endorsements)
            $pendingEndorsements = PolicyEndorsement::where('effective_date', '>', $today)
                ->whereDate('effective_date', '<=', $today->copy()->addDays(7)) // Next 7 days
                ->with(['policy', 'creator'])
                ->get();

            foreach ($pendingEndorsements as $endorsement) {
                $daysUntilEffective = $today->diffInDays($endorsement->effective_date);
                
                // Only create notification if endorsement has a creator
                if ($endorsement->creator_id && !$this->hasRecentNotification($endorsement->creator_id, 'ENDORSEMENT_PENDING', $endorsement->id)) {
                    Notification::create([
                        'user_id' => $endorsement->creator_id,
                        'type' => 'ENDORSEMENT_PENDING',
                        'title' => 'Endorsement Effective Soon',
                        'message' => "Endorsement {$endorsement->endorsement_number} for policy {$endorsement->policy->policy_number} becomes effective in {$daysUntilEffective} days.",
                        'priority' => 'MEDIUM',
                        'data' => [
                            'endorsement_id' => $endorsement->id,
                            'policy_id' => $endorsement->policy_id,
                            'effective_date' => $endorsement->effective_date,
                            'days_until_effective' => $daysUntilEffective
                        ],
                        'expires_at' => $endorsement->effective_date->copy()->addDays(7),
                    ]);
                }
            }

            // Check for endorsements that are effective today
            $todayEndorsements = PolicyEndorsement::whereDate('effective_date', $today)
                ->with(['policy', 'creator'])
                ->get();

            foreach ($todayEndorsements as $endorsement) {
                if (!$this->hasRecentNotification($endorsement->creator_id, 'ENDORSEMENT_EFFECTIVE', $endorsement->id)) {
                    Notification::create([
                        'user_id' => $endorsement->creator_id,
                        'type' => 'ENDORSEMENT_EFFECTIVE',
                        'title' => 'Endorsement Effective Today',
                        'message' => "Endorsement {$endorsement->endorsement_number} for policy {$endorsement->policy->policy_number} is effective today.",
                        'priority' => 'MEDIUM',
                        'data' => [
                            'endorsement_id' => $endorsement->id,
                            'policy_id' => $endorsement->policy_id,
                            'effective_date' => $endorsement->effective_date
                        ],
                        'expires_at' => $today->copy()->addDays(1),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checking endorsement alerts: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old notifications.
     */
    public function cleanupOldNotifications(): void
    {
        try {
            $cutoffDate = Carbon::now()->subDays(30);
            
            Notification::where('created_at', '<', $cutoffDate)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', Carbon::now())
                ->delete();

            // Also delete read notifications older than 7 days
            $readCutoffDate = Carbon::now()->subDays(7);
            Notification::whereNotNull('read_at')
                ->where('read_at', '<', $readCutoffDate)
                ->delete();
        } catch (\Exception $e) {
            Log::error('Error cleaning up old notifications: ' . $e->getMessage());
        }
    }

    /**
     * Create a policy expiry notification.
     */
    private function createPolicyExpiryNotification(InsurancePolicy $policy, string $priority, string $title, string $message): void
    {
        // Check if notification already exists for this policy and timeframe
        if ($this->hasRecentNotification($policy->creator_id, 'POLICY_EXPIRY_WARNING', $policy->id)) {
            return;
        }

        Notification::create([
            'user_id' => $policy->creator_id,
            'type' => 'POLICY_EXPIRY_WARNING',
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'data' => [
                'policy_id' => $policy->id,
                'policy_number' => $policy->policy_number,
                'insurance_type' => $policy->insurance_type,
                'provider' => $policy->provider,
                'end_date' => $policy->end_date,
                'days_until_expiry' => Carbon::now()->diffInDays($policy->end_date, false),
                'entities_covered' => $policy->entities->count(),
                'premium_amount' => $policy->premium_amount,
                'sum_insured' => $policy->sum_insured
            ],
            'expires_at' => $policy->end_date->copy()->addDays(7),
        ]);
    }

    /**
     * Check if a recent notification already exists for the given criteria.
     */
    private function hasRecentNotification(int $userId, string $type, int $entityId): bool
    {
        $oneDayAgo = Carbon::now()->subDay();
        
        return Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where(function($query) use ($entityId) {
                $query->whereJsonContains('data->policy_id', $entityId)
                      ->orWhereJsonContains('data->endorsement_id', $entityId);
            })
            ->where('created_at', '>=', $oneDayAgo)
            ->exists();
    }

    /**
     * Get summary statistics for notifications.
     */
    public function getNotificationStats(): array
    {
        $stats = [
            'total_notifications' => Notification::count(),
            'unread_notifications' => Notification::unread()->count(),
            'critical_notifications' => Notification::byPriority('CRITICAL')->unread()->count(),
            'high_notifications' => Notification::byPriority('HIGH')->unread()->count(),
            'medium_notifications' => Notification::byPriority('MEDIUM')->unread()->count(),
            'low_notifications' => Notification::byPriority('LOW')->unread()->count(),
            'policy_expiry_warnings' => Notification::ofType('POLICY_EXPIRY_WARNING')->unread()->count(),
            'endorsement_alerts' => Notification::ofType('ENDORSEMENT_PENDING')->unread()->count(),
        ];

        return $stats;
    }
}