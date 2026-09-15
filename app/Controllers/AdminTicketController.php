<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Services\AdminAuthService;
use App\Services\TicketService;

class AdminTicketController
{
    public function index(Request $request): void
    {
        $filters = [
            'status' => $request->input('status', ''),
            'priority' => $request->input('priority', ''),
            'search' => $request->input('search', ''),
        ];

        $tickets = TicketService::getAdminTickets($filters);

        // Counts for quick filter tabs
        $counts = [
            'all' => (int)(Database::fetch("SELECT COUNT(*) as c FROM `tickets`")['c'] ?? 0),
            'open' => (int)(Database::fetch("SELECT COUNT(*) as c FROM `tickets` WHERE `status` = 'open'")['c'] ?? 0),
            'customer_reply' => (int)(Database::fetch("SELECT COUNT(*) as c FROM `tickets` WHERE `status` = 'customer_reply'")['c'] ?? 0),
            'answered' => (int)(Database::fetch("SELECT COUNT(*) as c FROM `tickets` WHERE `status` = 'answered'")['c'] ?? 0),
            'closed' => (int)(Database::fetch("SELECT COUNT(*) as c FROM `tickets` WHERE `status` = 'closed'")['c'] ?? 0),
        ];

        View::render('admin/tickets/index', [
            'title' => 'Support Tickets Desk - Admin Panel',
            'tickets' => $tickets,
            'filters' => $filters,
            'counts' => $counts,
        ], 'admin');
    }

    public function show(Request $request, array $params): void
    {
        $ticketId = (int)($params['id'] ?? 0);
        $ticket = TicketService::getTicketWithMessages($ticketId);

        if (!$ticket) {
            Session::setFlash('error', 'Ticket not found.');
            Response::redirect('/admin/tickets');
            return;
        }

        // Fetch admins for assignment dropdown
        $admins = Database::query("SELECT `id`, `name`, `email` FROM `admins` WHERE `status` = 'active' ORDER BY `name` ASC");

        // Fetch user's recent orders for quick context
        $userRecentOrders = Database::query(
            "SELECT `id`, `service_name`, `quantity`, `charge`, `status`, `created_at` 
             FROM `orders` 
             WHERE `user_id` = :uid 
             ORDER BY `id` DESC LIMIT 5",
            [':uid' => $ticket['user_id']]
        );

        View::render('admin/tickets/show', [
            'title' => "Manage Ticket #{$ticketId} - Admin Panel",
            'ticket' => $ticket,
            'admins' => $admins,
            'userRecentOrders' => $userRecentOrders,
        ], 'admin');
    }

    public function reply(Request $request, array $params): void
    {
        $admin = AdminAuthService::user();
        $ticketId = (int)($params['id'] ?? 0);
        $message = trim((string)$request->input('message', ''));

        if (empty($message)) {
            Session::setFlash('error', 'Reply message cannot be empty.');
            Response::redirect('/admin/tickets/' . $ticketId);
            return;
        }

        $result = TicketService::addReply($ticketId, $message, 'admin', (int)$admin['id']);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', 'Your reply has been sent to the user.');
        }

        Response::redirect('/admin/tickets/' . $ticketId);
    }

    public function updateStatus(Request $request, array $params): void
    {
        $ticketId = (int)($params['id'] ?? 0);
        $status = (string)$request->input('status', '');

        if (TicketService::updateStatus($ticketId, $status)) {
            Session::setFlash('success', "Ticket status updated to '{$status}'.");
        } else {
            Session::setFlash('error', 'Invalid ticket status.');
        }

        Response::redirect('/admin/tickets/' . $ticketId);
    }

    public function assign(Request $request, array $params): void
    {
        $ticketId = (int)($params['id'] ?? 0);
        $adminId = (int)$request->input('admin_id', 0);

        TicketService::assignTicket($ticketId, $adminId > 0 ? $adminId : null);
        Session::setFlash('success', 'Ticket staff assignment updated.');

        Response::redirect('/admin/tickets/' . $ticketId);
    }
}
