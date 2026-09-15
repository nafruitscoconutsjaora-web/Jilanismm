<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Services\AuthService;
use App\Services\ReferralService;

class ReferralController
{
    public function index(Request $request): void
    {
        $user = AuthService::user();
        $data = ReferralService::getUserReferralData((int)$user['id']);

        $appUrl = rtrim(config('app.url', 'http://localhost:3000'), '/');
        $referralUrl = "{$appUrl}/register?ref=" . urlencode($data['referral_code']);

        View::render('user/referrals/index', [
            'title' => 'Affiliate & Referral Program - ' . config('app.name'),
            'user' => $user,
            'referralUrl' => $referralUrl,
            'data' => $data,
        ], 'user');
    }
}
