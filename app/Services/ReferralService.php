<?php

namespace App\Services;

use App\Database\Database;
use App\Services\NotificationService;
use App\Services\WalletService;

class ReferralService
{
    /**
     * Process referral commission when a user completes a qualifying deposit.
     */
    public static function processDepositCommission(int $userId, float $depositAmount, string $transactionId): ?array
    {
        // 1. Check if referral system is enabled in settings
        $enabledSetting = Database::fetch("SELECT `setting_value` FROM `settings` WHERE `setting_key` = 'referral_system_enabled' LIMIT 1");
        $isEnabled = $enabledSetting ? (bool)(int)$enabledSetting['setting_value'] : true;
        if (!$isEnabled) {
            return null;
        }

        // 2. Fetch referred user and their referrer ID
        $user = Database::fetch("SELECT `id`, `name`, `referred_by` FROM `users` WHERE `id` = :id LIMIT 1", [':id' => $userId]);
        if (!$user || empty($user['referred_by'])) {
            return null;
        }

        $referrerId = (int)$user['referred_by'];

        // Self-referral prevention
        if ($referrerId === $userId) {
            return null;
        }

        // 3. Fetch referrer details
        $referrer = Database::fetch("SELECT `id`, `name`, `status` FROM `users` WHERE `id` = :id LIMIT 1", [':id' => $referrerId]);
        if (!$referrer || $referrer['status'] !== 'active') {
            return null;
        }

        // 4. Check for duplicate commission for this specific transaction
        $existingLog = Database::fetch(
            "SELECT `id` FROM `referral_logs` WHERE `referrer_id` = :rid AND `event_reference` = :ref LIMIT 1",
            [':rid' => $referrerId, ':ref' => $transactionId]
        );
        if ($existingLog) {
            return null; // Already credited
        }

        // 5. Commission rate percentage
        $rateSetting = Database::fetch("SELECT `setting_value` FROM `settings` WHERE `setting_key` = 'referral_commission_percent' LIMIT 1");
        $commissionRate = $rateSetting ? (float)$rateSetting['setting_value'] : 5.00;
        if ($commissionRate <= 0) {
            return null;
        }

        $rewardAmount = round($depositAmount * ($commissionRate / 100.0), 4);
        if ($rewardAmount <= 0) {
            return null;
        }

        // 6. Credit referrer's wallet
        $creditRes = WalletService::credit(
            $referrerId,
            $rewardAmount,
            'referral_bonus',
            'REF-DEP-' . $transactionId,
            "Referral commission: {$commissionRate}% from {$user['name']}'s deposit (\${$depositAmount})"
        );

        if (!$creditRes['success']) {
            return null;
        }

        // 7. Insert referral log record
        Database::execute(
            "INSERT INTO `referral_logs` (
                `referrer_id`, `referred_id`, `qualifying_event`, `event_reference`,
                `order_amount`, `commission_rate`, `reward_amount`, `status`, `created_at`
             ) VALUES (
                :rid, :uid, 'deposit', :ref,
                :oamt, :crate, :ramt, 'credited', NOW()
             )",
            [
                ':rid' => $referrerId,
                ':uid' => $userId,
                ':ref' => $transactionId,
                ':oamt' => $depositAmount,
                ':crate' => $commissionRate,
                ':ramt' => $rewardAmount
            ]
        );

        // 8. Notify referrer
        $rewardFormatted = number_format($rewardAmount, 2);
        NotificationService::send(
            $referrerId,
            'Referral Commission Received!',
            "You received a \${$rewardFormatted} commission ({$commissionRate}%) from {$user['name']}'s successful deposit.",
            'success'
        );

        return [
            'referrer_id' => $referrerId,
            'referred_id' => $userId,
            'commission_rate' => $commissionRate,
            'reward_amount' => $rewardAmount,
            'transaction_id' => $transactionId
        ];
    }

    /**
     * Get user referral statistics and tracking data.
     */
    public static function getUserReferralData(int $userId): array
    {
        $user = Database::fetch("SELECT `id`, `name`, `referral_code` FROM `users` WHERE `id` = :id LIMIT 1", [':id' => $userId]);
        if (!$user) {
            return [];
        }

        // Generate referral code if missing
        $referralCode = $user['referral_code'];
        if (empty($referralCode)) {
            $referralCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            Database::execute("UPDATE `users` SET `referral_code` = :code WHERE `id` = :id", [':code' => $referralCode, ':id' => $userId]);
        }

        // System Commission Rate
        $rateSetting = Database::fetch("SELECT `setting_value` FROM `settings` WHERE `setting_key` = 'referral_commission_percent' LIMIT 1");
        $commissionRate = $rateSetting ? (float)$rateSetting['setting_value'] : 5.00;

        // Total referred users count
        $countRow = Database::fetch("SELECT COUNT(*) as total FROM `users` WHERE `referred_by` = :id", [':id' => $userId]);
        $totalReferred = (int)($countRow['total'] ?? 0);

        // Total earnings
        $earnRow = Database::fetch(
            "SELECT COALESCE(SUM(`reward_amount`), 0) as total_earnings FROM `referral_logs` WHERE `referrer_id` = :id AND `status` = 'credited'",
            [':id' => $userId]
        );
        $totalEarnings = (float)($earnRow['total_earnings'] ?? 0.0);

        // Recent referred users list
        $referredUsers = Database::query(
            "SELECT `id`, `name`, `email`, `created_at` FROM `users` WHERE `referred_by` = :id ORDER BY `id` DESC LIMIT 30",
            [':id' => $userId]
        );

        // Earnings history logs
        $logs = Database::query(
            "SELECT rl.*, u.name as referred_name 
             FROM `referral_logs` rl 
             JOIN `users` u ON rl.referred_id = u.id 
             WHERE rl.referrer_id = :id 
             ORDER BY rl.id DESC LIMIT 50",
            [':id' => $userId]
        );

        return [
            'referral_code' => $referralCode,
            'commission_rate' => $commissionRate,
            'total_referred' => $totalReferred,
            'total_earnings' => $totalEarnings,
            'referred_users' => $referredUsers,
            'logs' => $logs
        ];
    }

    /**
     * Admin Overview of referral network
     */
    public static function getAdminOverview(): array
    {
        return self::getAdminReferralOverview();
    }

    public static function getAdminReferralOverview(): array
    {
        $totals = Database::fetch(
            "SELECT 
                COUNT(*) as total_rewards_count,
                COALESCE(SUM(`reward_amount`), 0) as total_payouts,
                COALESCE(SUM(`order_amount`), 0) as total_volume_generated
             FROM `referral_logs` WHERE `status` = 'credited'"
        );

        $totalReferredUsers = Database::fetch("SELECT COUNT(*) as count FROM `users` WHERE `referred_by` IS NOT NULL");

        $topReferrers = Database::query(
            "SELECT 
                u.id, u.name, u.email, u.referral_code,
                COUNT(rl.id) as payouts_count,
                COALESCE(SUM(rl.reward_amount), 0) as total_earned,
                (SELECT COUNT(*) FROM `users` ref WHERE ref.referred_by = u.id) as total_invited
             FROM `users` u
             JOIN `referral_logs` rl ON u.id = rl.referrer_id
             WHERE rl.status = 'credited'
             GROUP BY u.id, u.name, u.email, u.referral_code
             ORDER BY total_earned DESC LIMIT 15"
        );

        $recentLogs = Database::query(
            "SELECT 
                rl.*, 
                ref.name as referrer_name, ref.email as referrer_email,
                usr.name as referred_name, usr.email as referred_email
             FROM `referral_logs` rl
             JOIN `users` ref ON rl.referrer_id = ref.id
             JOIN `users` usr ON rl.referred_id = usr.id
             ORDER BY rl.id DESC LIMIT 50"
        );

        return [
            'stats' => [
                'total_payouts' => (float)($totals['total_payouts'] ?? 0),
                'total_volume' => (float)($totals['total_volume_generated'] ?? 0),
                'total_rewards_count' => (int)($totals['total_rewards_count'] ?? 0),
                'total_referred_users' => (int)($totalReferredUsers['count'] ?? 0)
            ],
            'top_referrers' => $topReferrers,
            'recent_logs' => $recentLogs
        ];
    }
}
