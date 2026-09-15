<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\AuthService;
use App\Services\PaymentService;

class AdminPaymentController
{
    public function index(Request $request): void
    {
        $admin = AuthService::admin();
        $page = max(1, (int)$request->input('page', 1));
        $status = trim($request->input('status', 'all'));
        $search = trim($request->input('search', ''));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];

        if (!empty($status) && $status !== 'all') {
            $where[] = "p.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($search)) {
            $where[] = "(p.transaction_id LIKE :s_txn OR u.email LIKE :s_email OR u.name LIKE :s_name)";
            $params[':s_txn'] = "%{$search}%";
            $params[':s_email'] = "%{$search}%";
            $params[':s_name'] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as cnt FROM `payments` p JOIN `users` u ON p.user_id = u.id WHERE {$whereClause}",
            $params
        )['cnt'] ?? 0;

        $payments = Database::query(
            "SELECT p.*, g.name as gateway_name, g.code as gateway_code, u.name as user_name, u.email as user_email 
             FROM `payments` p 
             JOIN `payment_gateways` g ON p.gateway_id = g.id 
             JOIN `users` u ON p.user_id = u.id 
             WHERE {$whereClause} 
             ORDER BY p.id DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        View::render('admin/payments/index', [
            'title' => 'Payments & Transactions - Admin SMM Panel',
            'admin' => $admin,
            'payments' => $payments,
            'total' => $total,
            'page' => $page,
            'lastPage' => (int)ceil($total / $perPage),
            'status' => $status,
            'search' => $search
        ], 'admin');
    }

    public function approveManual(Request $request, array $params): void
    {
        $admin = AuthService::admin();
        $id = (int)($params['id'] ?? 0);

        $res = PaymentService::adminApproveManualPayment($id, (int)$admin['id']);

        if ($res['success']) {
            Session::setFlash('success', 'Manual payment approved and wallet credited successfully.');
        } else {
            Session::setFlash('error', $res['error'] ?? 'Approval failed.');
        }

        Response::redirect('/admin/payments');
    }

    public function rejectPayment(Request $request, array $params): void
    {
        $admin = AuthService::admin();
        $id = (int)($params['id'] ?? 0);
        $reason = trim($request->input('reason', 'Payment rejected by administrator'));

        $res = PaymentService::adminRejectPayment($id, (int)$admin['id'], $reason);

        if ($res['success']) {
            Session::setFlash('success', 'Payment was marked as rejected.');
        } else {
            Session::setFlash('error', $res['error'] ?? 'Action failed.');
        }

        Response::redirect('/admin/payments');
    }

    public function gateways(Request $request): void
    {
        $admin = AuthService::admin();
        $gateways = Database::query("SELECT * FROM `payment_gateways` ORDER BY `id` ASC");

        View::render('admin/payments/gateways', [
            'title' => 'Payment Gateways - Admin SMM Panel',
            'admin' => $admin,
            'gateways' => $gateways
        ], 'admin');
    }

    public function updateGateway(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $gateway = Database::fetch("SELECT * FROM `payment_gateways` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$gateway) {
            Session::setFlash('error', 'Payment gateway not found.');
            Response::redirect('/admin/gateways');
            return;
        }

        $name = trim($request->input('name', $gateway['name']));
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';
        $minAmount = (float)$request->input('min_amount', 1.0);
        $maxAmount = (float)$request->input('max_amount', 1000.0);
        $feePct = (float)$request->input('fee_percentage', 0.0);
        $feeFixed = (float)$request->input('fee_fixed', 0.0);
        $instructions = trim($request->input('instructions', ''));
        $credentialsJson = trim($request->input('credentials', ''));

        // Validate JSON credentials if provided
        if (!empty($credentialsJson)) {
            $decoded = json_decode($credentialsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Session::setFlash('error', 'Credentials must be valid JSON: ' . json_last_error_msg());
                Response::redirect('/admin/gateways');
                return;
            }
        } else {
            $credentialsJson = $gateway['credentials'];
        }

        Database::execute(
            "UPDATE `payment_gateways` SET 
                `name` = :name, `status` = :st, `min_amount` = :min, `max_amount` = :max,
                `fee_percentage` = :fpct, `fee_fixed` = :ffix, `instructions` = :inst,
                `credentials` = :creds, `updated_at` = NOW() 
             WHERE `id` = :id",
            [
                ':name' => $name,
                ':st' => $status,
                ':min' => $minAmount,
                ':max' => $maxAmount,
                ':fpct' => $feePct,
                ':ffix' => $feeFixed,
                ':inst' => $instructions,
                ':creds' => $credentialsJson,
                ':id' => $id
            ]
        );

        Session::setFlash('success', "Gateway '{$name}' configuration updated.");
        Response::redirect('/admin/gateways');
    }
}
