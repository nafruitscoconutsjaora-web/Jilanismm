<?php
/** @var array $tickets */
/** @var array $filters */
/** @var array $counts */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Support Tickets Desk</h1>
            <p class="text-sm text-zinc-500 mt-1">Manage customer support tickets, answer questions, troubleshoot orders, and track resolution metrics.</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 pb-3">
        <a href="/admin/tickets" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors <?= empty($filters['status']) ? 'bg-rose-600 text-white shadow-xs' : 'bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50' ?>">
            All Tickets (<?= (int)$counts['all'] ?>)
        </a>
        <a href="/admin/tickets?status=open" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors <?= ($filters['status'] ?? '') === 'open' ? 'bg-amber-500 text-white shadow-xs' : 'bg-white border border-zinc-200 text-amber-700 hover:bg-amber-50' ?>">
            Open / Pending (<?= (int)$counts['open'] ?>)
        </a>
        <a href="/admin/tickets?status=customer_reply" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors <?= ($filters['status'] ?? '') === 'customer_reply' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white border border-zinc-200 text-blue-700 hover:bg-blue-50' ?>">
            Customer Replied (<?= (int)$counts['customer_reply'] ?>)
        </a>
        <a href="/admin/tickets?status=answered" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors <?= ($filters['status'] ?? '') === 'answered' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border border-zinc-200 text-emerald-700 hover:bg-emerald-50' ?>">
            Answered (<?= (int)$counts['answered'] ?>)
        </a>
        <a href="/admin/tickets?status=closed" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors <?= ($filters['status'] ?? '') === 'closed' ? 'bg-zinc-800 text-white shadow-xs' : 'bg-white border border-zinc-200 text-zinc-600 hover:bg-zinc-50' ?>">
            Closed (<?= (int)$counts['closed'] ?>)
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="/admin/tickets" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="hidden" name="status" value="<?= e($filters['status'] ?? '') ?>">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search ticket #, subject, customer name or email..." class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>
            <div>
                <select name="priority" class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    <option value="">All Priorities</option>
                    <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High Priority</option>
                    <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium Priority</option>
                    <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low Priority</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                <a href="/admin/tickets" class="px-3 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-xs font-semibold rounded-xl transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-4">Ticket</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Subject & Category</th>
                        <th class="py-3 px-3 text-center">Priority</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Replies</th>
                        <th class="py-3 px-4">Assigned To</th>
                        <th class="py-3 px-4 text-right">Last Updated</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="9" class="py-10 text-center text-zinc-400">No support tickets match the selected filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <?php
                            $statusClasses = [
                                'open' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'customer_reply' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'answered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'closed' => 'bg-zinc-100 text-zinc-600 border-zinc-200'
                            ];
                            $statusLabels = [
                                'open' => 'Open',
                                'customer_reply' => 'Customer Reply',
                                'answered' => 'Answered',
                                'closed' => 'Closed'
                            ];
                            ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-zinc-900">
                                    #<?= (int)$t['id'] ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-zinc-900"><?= e($t['user_name']) ?></div>
                                    <div class="text-[10px] text-zinc-400"><?= e($t['user_email']) ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="/admin/tickets/<?= (int)$t['id'] ?>" class="font-bold text-zinc-900 hover:text-rose-600 transition-colors">
                                        <?= e($t['subject']) ?>
                                    </a>
                                    <div class="flex items-center space-x-2 text-[10px] text-zinc-400 mt-0.5">
                                        <span class="font-semibold text-zinc-600"><?= e($t['category']) ?></span>
                                        <?php if (!empty($t['order_id'])): ?>
                                            <span>•</span>
                                            <span class="text-rose-600 font-medium">Order #<?= (int)$t['order_id'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase <?= $t['priority'] === 'high' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($t['priority'] === 'medium' ? 'bg-zinc-100 text-zinc-700' : 'bg-zinc-50 text-zinc-500') ?>">
                                        <?= e($t['priority']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border <?= $statusClasses[$t['status']] ?? 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                        <?= $statusLabels[$t['status']] ?? ucfirst($t['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-zinc-700">
                                    <?= (int)$t['message_count'] ?>
                                </td>
                                <td class="py-3 px-4 text-zinc-600 text-[11px]">
                                    <?= !empty($t['assigned_admin_name']) ? e($t['assigned_admin_name']) : '<span class="text-zinc-400 italic">Unassigned</span>' ?>
                                </td>
                                <td class="py-3 px-4 text-right text-zinc-400 whitespace-nowrap">
                                    <?= date('M d, H:i', strtotime($t['updated_at'])) ?>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="/admin/tickets/<?= (int)$t['id'] ?>" class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-lg transition-colors inline-block shadow-xs">
                                        Manage &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
