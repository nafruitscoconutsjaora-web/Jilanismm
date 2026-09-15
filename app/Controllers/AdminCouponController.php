<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\CouponService;

class AdminCouponController
{
    public function index(Request $request): void
    {
        $filters = [
            'status' => $request->input('status', ''),
            'search' => $request->input('search', ''),
        ];

        $coupons = CouponService::getAllCoupons($filters);

        View::render('admin/coupons/index', [
            'title' => 'Coupon Management - Admin Panel',
            'coupons' => $coupons,
            'filters' => $filters,
        ], 'admin');
    }

    public function create(Request $request): void
    {
        $data = [
            'code' => $request->input('code'),
            'type' => $request->input('type', 'fixed'),
            'amount' => $request->input('amount'),
            'min_order_amount' => $request->input('min_order_amount', 0),
            'max_discount' => $request->input('max_discount'),
            'start_date' => $request->input('start_date'),
            'expires_at' => $request->input('expires_at'),
            'max_uses' => $request->input('max_uses', 0),
            'per_user_limit' => $request->input('per_user_limit', 1),
            'status' => $request->input('status', 'active'),
        ];

        $result = CouponService::createCoupon($data);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', 'Coupon code ' . strtoupper(trim($data['code'])) . ' created successfully.');
        }

        Response::redirect('/admin/coupons');
    }

    public function update(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);

        $data = [
            'code' => $request->input('code'),
            'type' => $request->input('type', 'fixed'),
            'amount' => $request->input('amount'),
            'min_order_amount' => $request->input('min_order_amount', 0),
            'max_discount' => $request->input('max_discount'),
            'start_date' => $request->input('start_date'),
            'expires_at' => $request->input('expires_at'),
            'max_uses' => $request->input('max_uses', 0),
            'per_user_limit' => $request->input('per_user_limit', 1),
            'status' => $request->input('status', 'active'),
        ];

        $result = CouponService::updateCoupon($id, $data);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', 'Coupon updated successfully.');
        }

        Response::redirect('/admin/coupons');
    }

    public function toggle(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $result = CouponService::toggleStatus($id);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', 'Coupon status changed to ' . $result['new_status'] . '.');
        }

        Response::redirect('/admin/coupons');
    }

    public function delete(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $result = CouponService::deleteCoupon($id);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', $result['message']);
        }

        Response::redirect('/admin/coupons');
    }

    public function usage(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $coupon = CouponService::getCouponById($id);

        if (!$coupon) {
            Session::setFlash('error', 'Coupon not found.');
            Response::redirect('/admin/coupons');
            return;
        }

        $history = CouponService::getCouponUsageHistory($id);

        View::render('admin/coupons/usage', [
            'title' => "Usage History: {$coupon['code']} - Admin Panel",
            'coupon' => $coupon,
            'history' => $history,
        ], 'admin');
    }
}
