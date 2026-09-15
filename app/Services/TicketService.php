<?php

namespace App\Services;

use App\Database\Database;
use App\Services\NotificationService;

class TicketService
{
    public const VALID_CATEGORIES = ['Order Issue', 'Payment & Deposit', 'Service Inquiry', 'API & Technical', 'Bug Report', 'Other'];
    public const VALID_PRIORITIES = ['low', 'medium', 'high'];
    public const VALID_STATUSES = ['open', 'answered', 'customer_reply', 'closed'];

    /**
     * Create a new support ticket and its initial message.
     */
    public static function createTicket(int $userId, array $data): array
    {
        $subject = trim($data['subject'] ?? '');
        $category = trim($data['category'] ?? 'Order Issue');
        $priority = in_array($data['priority'] ?? '', self::VALID_PRIORITIES, true) ? $data['priority'] : 'medium';
        $message = trim($data['message'] ?? '');
        $orderId = !empty($data['order_id']) ? (int)$data['order_id'] : null;

        if (empty($subject)) {
            return ['success' => false, 'error' => 'Please enter a ticket subject.'];
        }

        if (strlen($subject) > 250) {
            return ['success' => false, 'error' => 'Subject cannot exceed 250 characters.'];
        }

        if (empty($message)) {
            return ['success' => false, 'error' => 'Please describe your request or issue in detail.'];
        }

        // Validate order_id if provided
        if ($orderId !== null) {
            $order = Database::fetch("SELECT `id` FROM `orders` WHERE `id` = :oid AND `user_id` = :uid LIMIT 1", [
                ':oid' => $orderId,
                ':uid' => $userId
            ]);
            if (!$order) {
                return ['success' => false, 'error' => "Associated Order #{$orderId} was not found on your account."];
            }
        }

        Database::beginTransaction();
        try {
            Database::execute(
                "INSERT INTO `tickets` (`user_id`, `subject`, `category`, `order_id`, `priority`, `status`, `created_at`, `updated_at`)
                 VALUES (:uid, :sub, :cat, :oid, :pri, 'open', NOW(), NOW())",
                [
                    ':uid' => $userId,
                    ':sub' => $subject,
                    ':cat' => $category,
                    ':oid' => $orderId,
                    ':pri' => $priority
                ]
            );

            $ticketId = (int)Database::lastInsertId();

            Database::execute(
                "INSERT INTO `ticket_messages` (`ticket_id`, `sender_type`, `sender_id`, `message`, `created_at`)
                 VALUES (:tid, 'user', :uid, :msg, NOW())",
                [
                    ':tid' => $ticketId,
                    ':uid' => $userId,
                    ':msg' => $message
                ]
            );

            Database::commit();

            return ['success' => true, 'ticket_id' => $ticketId];
        } catch (\Throwable $e) {
            Database::rollBack();
            return ['success' => false, 'error' => 'Failed to open ticket: ' . $e->getMessage()];
        }
    }

    /**
     * Get tickets for a specific user with filtering.
     */
    public static function getUserTickets(int $userId, array $filters = []): array
    {
        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM `ticket_messages` tm WHERE tm.ticket_id = t.id) as message_count,
                       (SELECT tm2.created_at FROM `ticket_messages` tm2 WHERE tm2.ticket_id = t.id ORDER BY tm2.id DESC LIMIT 1) as last_reply_at
                FROM `tickets` t 
                WHERE t.user_id = :uid";
        $params = [':uid' => $userId];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (t.subject LIKE :search OR t.id = :search_id)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search_id'] = (int)$filters['search'];
        }

        $sql .= " ORDER BY t.updated_at DESC";

        return Database::query($sql, $params);
    }

    /**
     * Retrieve single ticket with all messages, enforcing user authorization.
     */
    public static function getTicketWithMessages(int $ticketId, ?int $userId = null): ?array
    {
        $ticketSql = "SELECT t.*, u.name as user_name, u.email as user_email,
                             adm.name as assigned_admin_name
                      FROM `tickets` t
                      JOIN `users` u ON t.user_id = u.id
                      LEFT JOIN `admins` adm ON t.assigned_to = adm.id
                      WHERE t.id = :id";
        $params = [':id' => $ticketId];

        if ($userId !== null) {
            $ticketSql .= " AND t.user_id = :uid";
            $params[':uid'] = $userId;
        }

        $ticket = Database::fetch($ticketSql, $params);
        if (!$ticket) {
            return null;
        }

        // Fetch messages
        $messages = Database::query(
            "SELECT tm.*, 
                    CASE 
                        WHEN tm.sender_type = 'user' THEN u.name
                        ELSE COALESCE(adm.name, 'Support Team')
                    END as sender_name
             FROM `ticket_messages` tm
             LEFT JOIN `users` u ON (tm.sender_type = 'user' AND tm.sender_id = u.id)
             LEFT JOIN `admins` adm ON (tm.sender_type = 'admin' AND tm.sender_id = adm.id)
             WHERE tm.ticket_id = :tid 
             ORDER BY tm.id ASC",
            [':tid' => $ticketId]
        );

        $ticket['messages'] = $messages;
        return $ticket;
    }

    /**
     * Post a reply to an existing ticket.
     */
    public static function addReply(int $ticketId, string $message, string $senderType, int $senderId): array
    {
        $cleanMsg = trim($message);
        if (empty($cleanMsg)) {
            return ['success' => false, 'error' => 'Reply message cannot be empty.'];
        }

        $ticket = Database::fetch("SELECT * FROM `tickets` WHERE `id` = :id LIMIT 1", [':id' => $ticketId]);
        if (!$ticket) {
            return ['success' => false, 'error' => 'Ticket not found.'];
        }

        if ($senderType === 'user' && (int)$ticket['user_id'] !== $senderId) {
            return ['success' => false, 'error' => 'Unauthorized.'];
        }

        $newStatus = ($senderType === 'admin') ? 'answered' : 'customer_reply';

        Database::beginTransaction();
        try {
            Database::execute(
                "INSERT INTO `ticket_messages` (`ticket_id`, `sender_type`, `sender_id`, `message`, `created_at`)
                 VALUES (:tid, :stype, :sid, :msg, NOW())",
                [
                    ':tid' => $ticketId,
                    ':stype' => $senderType,
                    ':sid' => $senderId,
                    ':msg' => $cleanMsg
                ]
            );

            Database::execute(
                "UPDATE `tickets` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id",
                [':st' => $newStatus, ':id' => $ticketId]
            );

            Database::commit();

            // Send notification to customer if admin replied
            if ($senderType === 'admin') {
                NotificationService::send(
                    (int)$ticket['user_id'],
                    "Support Ticket #{$ticketId} Replied",
                    "A support specialist has responded to your ticket: '{$ticket['subject']}'.",
                    'info'
                );
            }

            return ['success' => true, 'new_status' => $newStatus];
        } catch (\Throwable $e) {
            Database::rollBack();
            return ['success' => false, 'error' => 'Failed to submit reply: ' . $e->getMessage()];
        }
    }

    /**
     * Close a ticket.
     */
    public static function closeTicket(int $ticketId, ?int $userId = null): array
    {
        $ticket = Database::fetch("SELECT * FROM `tickets` WHERE `id` = :id LIMIT 1", [':id' => $ticketId]);
        if (!$ticket) {
            return ['success' => false, 'error' => 'Ticket not found.'];
        }

        if ($userId !== null && (int)$ticket['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'Unauthorized.'];
        }

        Database::execute("UPDATE `tickets` SET `status` = 'closed', `updated_at` = NOW() WHERE `id` = :id", [':id' => $ticketId]);

        return ['success' => true];
    }

    /**
     * Reopen a closed ticket.
     */
    public static function reopenTicket(int $ticketId, ?int $userId = null): array
    {
        $ticket = Database::fetch("SELECT * FROM `tickets` WHERE `id` = :id LIMIT 1", [':id' => $ticketId]);
        if (!$ticket) {
            return ['success' => false, 'error' => 'Ticket not found.'];
        }

        if ($userId !== null && (int)$ticket['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'Unauthorized.'];
        }

        Database::execute("UPDATE `tickets` SET `status` = 'open', `updated_at` = NOW() WHERE `id` = :id", [':id' => $ticketId]);

        return ['success' => true];
    }

    /**
     * Admin: Fetch all tickets with statistics and filters.
     */
    public static function getAdminTickets(array $filters = []): array
    {
        $sql = "SELECT t.*, u.name as user_name, u.email as user_email,
                       adm.name as assigned_admin_name,
                       (SELECT COUNT(*) FROM `ticket_messages` tm WHERE tm.ticket_id = t.id) as message_count,
                       (SELECT tm2.created_at FROM `ticket_messages` tm2 WHERE tm2.ticket_id = t.id ORDER BY tm2.id DESC LIMIT 1) as last_reply_at
                FROM `tickets` t
                JOIN `users` u ON t.user_id = u.id
                LEFT JOIN `admins` adm ON t.assigned_to = adm.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :st";
            $params[':st'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = :pri";
            $params[':pri'] = $filters['priority'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (t.subject LIKE :search OR u.name LIKE :search OR u.email LIKE :search OR t.id = :search_id)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search_id'] = (int)$filters['search'];
        }

        $sql .= " ORDER BY 
                    CASE t.status 
                        WHEN 'open' THEN 1 
                        WHEN 'customer_reply' THEN 2 
                        WHEN 'answered' THEN 3 
                        WHEN 'closed' THEN 4 
                        ELSE 5 
                    END, 
                    t.updated_at DESC";

        return Database::query($sql, $params);
    }

    /**
     * Admin: Assign ticket to staff member
     */
    public static function assignTicket(int $ticketId, ?int $adminId): bool
    {
        return Database::execute(
            "UPDATE `tickets` SET `assigned_to` = :aid, `updated_at` = NOW() WHERE `id` = :id",
            [':aid' => $adminId ?: null, ':id' => $ticketId]
        );
    }

    /**
     * Admin: Change ticket status directly
     */
    public static function updateStatus(int $ticketId, string $status): bool
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            return false;
        }
        return Database::execute(
            "UPDATE `tickets` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id",
            [':st' => $status, ':id' => $ticketId]
        );
    }
}
