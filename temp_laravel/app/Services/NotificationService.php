<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Create a new notification
     *
     * @param string $type Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $priority Notification priority (LOW, MEDIUM, HIGH, CRITICAL)
     * @param array $data Additional data for the notification
     * @param int|null $userId User ID (defaults to authenticated user)
     * @param int|null $expiresInDays Days until notification expires (defaults to 30)
     * @return Notification
     */
    public static function create(
        string $type,
        string $title,
        string $message,
        string $priority = 'MEDIUM',
        array $data = [],
        ?int $userId = null,
        ?int $expiresInDays = 30
    ): Notification {
        $userId = $userId ?? Auth::id();

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'data' => $data,
            'expires_at' => now()->addDays($expiresInDays),
            'is_active' => true,
        ]);
    }

    /**
     * Create notification for entity operations
     */
    public static function forEntity(
        string $action,
        string $entityType,
        string $entityName,
        string $entityId,
        array $additionalData = []
    ): Notification {
        $titles = [
            'CREATE' => ucfirst(strtolower($entityType)) . ' Created',
            'UPDATE' => ucfirst(strtolower($entityType)) . ' Updated',
            'DELETE' => ucfirst(strtolower($entityType)) . ' Deleted',
            'BULK_UPDATE' => 'Bulk ' . ucfirst(strtolower($entityType)) . ' Update',
            'BULK_DELETE' => 'Bulk ' . ucfirst(strtolower($entityType)) . ' Delete',
        ];

        $messages = [
            'CREATE' => "New {$entityType} '{$entityName}' has been created successfully.",
            'UPDATE' => "{$entityType} '{$entityName}' has been updated successfully.",
            'DELETE' => "{$entityType} '{$entityName}' has been deleted successfully.",
            'BULK_UPDATE' => "Bulk update completed for {$entityType} entities.",
            'BULK_DELETE' => "Bulk deletion completed for {$entityType} entities.",
        ];

        return self::create(
            'ENTITY_' . strtoupper($action),
            $titles[$action] ?? 'Entity Operation',
            $messages[$action] ?? "Entity operation '{$action}' completed.",
            self::getPriorityForAction($action),
            array_merge([
                'entity_type' => $entityType,
                'entity_name' => $entityName,
                'entity_id' => $entityId,
                'action' => $action,
            ], $additionalData)
        );
    }

    /**
     * Create notification for policy operations
     */
    public static function forPolicy(
        string $action,
        string $policyNumber,
        string $insuranceType,
        string $provider,
        array $additionalData = []
    ): Notification {
        $titles = [
            'CREATE' => 'Policy Created',
            'UPDATE' => 'Policy Updated',
            'DELETE' => 'Policy Deleted',
            'STATUS_CHANGE' => 'Policy Status Changed',
            'ADD_ENTITY' => 'Entity Added to Policy',
            'REMOVE_ENTITY' => 'Entity Removed from Policy',
            'UPLOAD_DOCUMENT' => 'Policy Document Uploaded',
            'BULK_UPDATE' => 'Bulk Policy Update',
            'BULK_DELETE' => 'Bulk Policy Delete',
        ];

        $messages = [
            'CREATE' => "Policy {$policyNumber} ({$insuranceType}) with {$provider} has been created successfully.",
            'UPDATE' => "Policy {$policyNumber} ({$insuranceType}) has been updated successfully.",
            'DELETE' => "Policy {$policyNumber} ({$insuranceType}) has been deleted successfully.",
            'STATUS_CHANGE' => "Policy {$policyNumber} status has been changed to " . ($additionalData['new_status'] ?? 'unknown') . ".",
            'ADD_ENTITY' => "Entity has been added to policy {$policyNumber}.",
            'REMOVE_ENTITY' => "Entity has been removed from policy {$policyNumber}.",
            'UPLOAD_DOCUMENT' => "New document has been uploaded to policy {$policyNumber}.",
            'BULK_UPDATE' => "Bulk update completed for policy operations.",
            'BULK_DELETE' => "Bulk deletion completed for policy operations.",
        ];

        return self::create(
            'POLICY_' . strtoupper($action),
            $titles[$action] ?? 'Policy Operation',
            $messages[$action] ?? "Policy operation '{$action}' completed.",
            self::getPriorityForAction($action),
            array_merge([
                'policy_number' => $policyNumber,
                'insurance_type' => $insuranceType,
                'provider' => $provider,
                'action' => $action,
            ], $additionalData)
        );
    }

    /**
     * Create notification for endorsement operations
     */
    public static function forEndorsement(
        string $action,
        string $endorsementNumber,
        string $policyNumber,
        string $description = '',
        array $additionalData = []
    ): Notification {
        $titles = [
            'CREATE' => 'Endorsement Created',
            'UPDATE' => 'Endorsement Updated',
            'DELETE' => 'Endorsement Deleted',
            'ADD_ENTITY' => 'Entity Added via Endorsement',
            'REMOVE_ENTITY' => 'Entity Removed via Endorsement',
            'UPLOAD_DOCUMENT' => 'Endorsement Document Uploaded',
            'BULK_DELETE' => 'Bulk Endorsement Delete',
        ];

        $messages = [
            'CREATE' => "Endorsement {$endorsementNumber} for policy {$policyNumber} has been created successfully.",
            'UPDATE' => "Endorsement {$endorsementNumber} for policy {$policyNumber} has been updated successfully.",
            'DELETE' => "Endorsement {$endorsementNumber} for policy {$policyNumber} has been deleted successfully.",
            'ADD_ENTITY' => "Entity has been added via endorsement {$endorsementNumber}.",
            'REMOVE_ENTITY' => "Entity has been removed via endorsement {$endorsementNumber}.",
            'UPLOAD_DOCUMENT' => "New document has been uploaded to endorsement {$endorsementNumber}.",
            'BULK_DELETE' => "Bulk deletion completed for endorsement operations.",
        ];

        return self::create(
            'ENDORSEMENT_' . strtoupper($action),
            $titles[$action] ?? 'Endorsement Operation',
            $messages[$action] ?? "Endorsement operation '{$action}' completed.",
            self::getPriorityForAction($action),
            array_merge([
                'endorsement_number' => $endorsementNumber,
                'policy_number' => $policyNumber,
                'description' => $description,
                'action' => $action,
            ], $additionalData)
        );
    }

    /**
     * Get priority level for action type
     */
    private static function getPriorityForAction(string $action): string
    {
        $highPriorityActions = ['DELETE', 'BULK_DELETE', 'STATUS_CHANGE'];
        $criticalPriorityActions = [];

        if (in_array($action, $criticalPriorityActions)) {
            return 'CRITICAL';
        }

        if (in_array($action, $highPriorityActions)) {
            return 'HIGH';
        }

        return 'MEDIUM';
    }

    /**
     * Create notification for multiple users (admin/system notifications)
     */
    public static function createForMultipleUsers(
        array $userIds,
        string $type,
        string $title,
        string $message,
        string $priority = 'MEDIUM',
        array $data = [],
        ?int $expiresInDays = 30
    ): array {
        $notifications = [];

        foreach ($userIds as $userId) {
            $notifications[] = self::create(
                $type,
                $title,
                $message,
                $priority,
                $data,
                $userId,
                $expiresInDays
            );
        }

        return $notifications;
    }

    /**
     * Create system-wide notification for all active users
     */
    public static function createSystemNotification(
        string $type,
        string $title,
        string $message,
        string $priority = 'MEDIUM',
        array $data = [],
        ?int $expiresInDays = 30
    ): array {
        $activeUserIds = User::where('status', 'ACTIVE')->pluck('id')->toArray();

        return self::createForMultipleUsers(
            $activeUserIds,
            $type,
            $title,
            $message,
            $priority,
            $data,
            $expiresInDays
        );
    }

    /**
     * Clean up expired notifications
     */
    public static function cleanupExpiredNotifications(): int
    {
        return Notification::where('expires_at', '<', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Get notification statistics for dashboard
     */
    public static function getNotificationStats(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();

        return [
            'total' => Notification::where('user_id', $userId)->active()->count(),
            'unread' => Notification::where('user_id', $userId)->active()->unread()->count(),
            'critical' => Notification::where('user_id', $userId)->active()->byPriority('CRITICAL')->count(),
            'high' => Notification::where('user_id', $userId)->active()->byPriority('HIGH')->count(),
            'medium' => Notification::where('user_id', $userId)->active()->byPriority('MEDIUM')->count(),
            'low' => Notification::where('user_id', $userId)->active()->byPriority('LOW')->count(),
        ];
    }
}