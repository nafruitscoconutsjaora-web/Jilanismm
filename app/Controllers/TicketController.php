<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\TicketService;

class TicketController
{
    public function index(Request $request): void
    {
        $user = AuthService::user();
        $filters = [
            'status' => $request->input('status', ''),
            'search' => $request->input('search', ''),
        ];

        $tickets = TicketService::getUserTickets((int)$user['id'], $filters);

        View::render('user/tickets/index', [
            'title' => 'Support Tickets - ' . config('app.name'),
            'user' => $user,
            'tickets' => $tickets,
            'filters' => $filters,
        ], 'user');
    }

    public function showCreate(Request $request): void
    {
        $user = AuthService::user();
        $preselectedOrderId = (int)$request->input('order_id', 0);

        View::render('user/tickets/create', [
            'title' => 'Open New Support Ticket - ' . config('app.name'),
            'user' => $user,
            'categories' => TicketService::VALID_CATEGORIES,
            'preselectedOrderId' => $preselectedOrderId > 0 ? $preselectedOrderId : null,
        ], 'user');
    }

    public function create(Request $request): void
    {
        $user = AuthService::user();
        $data = [
            'subject' => $request->input('subject'),
            'category' => $request->input('category'),
            'priority' => $request->input('priority', 'medium'),
            'order_id' => $request->input('order_id'),
            'message' => $request->input('message'),
        ];

        $result = TicketService::createTicket((int)$user['id'], $data);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
            Session::setOld($data);
            Response::redirect('/tickets/create');
            return;
        }

        Session::clearOld();
        Session::setFlash('success', "Ticket #{$result['ticket_id']} has been submitted. Our support team will respond shortly.");
        Response::redirect('/tickets/' . $result['ticket_id']);
    }

    public function show(Request $request, array $params): void
    {
        $user = AuthService::user();
        $ticketId = (int)($params['id'] ?? 0);

        $ticket = TicketService::getTicketWithMessages($ticketId, (int)$user['id']);

        if (!$ticket) {
            Session::setFlash('error', 'Support ticket not found or access denied.');
            Response::redirect('/tickets');
            return;
        }

        View::render('user/tickets/show', [
            'title' => "Ticket #{$ticketId}: {$ticket['subject']} - " . config('app.name'),
            'user' => $user,
            'ticket' => $ticket,
        ], 'user');
    }

    public function reply(Request $request, array $params): void
    {
        $user = AuthService::user();
        $ticketId = (int)($params['id'] ?? 0);
        $message = trim((string)$request->input('message', ''));

        if (empty($message)) {
            Session::setFlash('error', 'Reply message cannot be empty.');
            Response::redirect('/tickets/' . $ticketId);
            return;
        }

        $result = TicketService::addReply($ticketId, $message, 'user', (int)$user['id']);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', 'Your reply was submitted successfully.');
        }

        Response::redirect('/tickets/' . $ticketId);
    }

    public function close(Request $request, array $params): void
    {
        $user = AuthService::user();
        $ticketId = (int)($params['id'] ?? 0);

        $result = TicketService::closeTicket($ticketId, (int)$user['id']);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', "Ticket #{$ticketId} was marked as closed.");
        }

        Response::redirect('/tickets/' . $ticketId);
    }

    public function reopen(Request $request, array $params): void
    {
        $user = AuthService::user();
        $ticketId = (int)($params['id'] ?? 0);

        $result = TicketService::reopenTicket($ticketId, (int)$user['id']);

        if (!$result['success']) {
            Session::setFlash('error', $result['error']);
        } else {
            Session::setFlash('success', "Ticket #{$ticketId} has been reopened.");
        }

        Response::redirect('/tickets/' . $ticketId);
    }
}
