<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Services\ReferralService;

class AdminReferralController
{
    public function index(Request $request): void
    {
        $overview = ReferralService::getAdminReferralOverview();

        $settings = [
            'enabled' => Database::fetch("SELECT `setting_value` FROM `settings` WHERE `setting_key` = 'referral_system_enabled' LIMIT 1")['setting_value'] ?? '1',
            'commission_percent' => Database::fetch("SELECT `setting_value` FROM `settings` WHERE `setting_key` = 'referral_commission_percent' LIMIT 1")['setting_value'] ?? '5.00',
        ];

        View::render('admin/referrals/index', [
            'title' => 'Affiliate & Referral Network - Admin Panel',
            'overview' => $overview,
            'settings' => $settings,
        ], 'admin');
    }

    public function updateSettings(Request $request): void
    {
        $enabled = $request->input('referral_system_enabled', '0') === '1' ? '1' : '0';
        $percent = (float)$request->input('referral_commission_percent', 5.0);

        if ($percent < 0 || $percent > 100) {
            Session::setFlash('error', 'Commission percentage must be between 0% and 100%.');
            Response::redirect('/admin/referrals');
            return;
        }

        Database::execute(
            "INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `updated_at`) 
             VALUES ('referral_system_enabled', :val, 'referral', NOW()) 
             ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `updated_at` = NOW()",
            [':val' => $enabled]
        );

        Database::execute(
            "INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `updated_at`) 
             VALUES ('referral_commission_percent', :val, 'referral', NOW()) 
             ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `updated_at` = NOW()",
            [':val' => number_format($percent, 2, '.', '')]
        );

        Session::setFlash('success', 'Affiliate and referral system configuration updated.');
        Response::redirect('/admin/referrals');
    }
}
