<?php
/** @var array $user */
/** @var array $tickets */
/** @var array $filters */
?>

<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Support Center</h1>
            <p class="text-sm text-zinc-500 mt-1">Need help with an order, payment, or custom request? Our support team is ready to assist.</p>
        </div>
        <a href="/tickets/create" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Open New Ticket
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="/tickets" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-zinc-500 uppercase tracking-wider mb-1">Search Tickets</label>
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by subject or ticket #..." class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-zinc-500 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    <option value="">All Statuses</option>
                    <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open (Pending Support)</option>
                    <option value="answered" <?= ($filters['status'] ?? '') === 'answered' ? 'selected' : '' ?>>Answered (Staff Replied)</option>
                    <option value="customer_reply" <?= ($filters['status'] ?? '') === 'customer_reply' ? 'selected' : '' ?>>Customer Replied</option>
                    <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                <a href="/tickets" class="px-3 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-xs font-semibold rounded-xl transition-colors">
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
                        <th class="py-3 px-5">Ticket ID</th>
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4 text-center">Priority</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Messages</th>
                        <th class="py-3 px-4 text-right">Last Activity</th>
                        <th class="py-3 px-5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="8" class="py-10 text-center text-zinc-400">
                                <svg class="h-8 w-8 mx-auto mb-2 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                No support tickets found. Click "Open New Ticket" if you need any help.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <?php
                            $statusClasses = [
                                'open' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'answered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'customer_reply' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'closed' => 'bg-zinc-100 text-zinc-600 border-zinc-200'
                            ];
                            $statusLabels = [
                                'open' => 'Open',
                                'answered' => 'Answered',
                                'customer_reply' => 'Customer Replied',
                                'closed' => 'Closed'
                            ];
                            ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-5 font-mono font-bold text-zinc-900">
                                    #<?= (int)$t['id'] ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-zinc-900">
                                    <a href="/tickets/<?= (int)$t['id'] ?>" class="hover:text-rose-600 transition-colors">
                                        <?= e($t['subject']) ?>
                                    </a>
                                    <?php if (!empty($t['order_id'])): ?>
                                        <span class="block text-[10px] text-zinc-400 font-normal">Related Order #<?= (int)$t['order_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600">
                                    <?= e($t['category'] ?? 'General') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase <?= $t['priority'] === 'high' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($t['priority'] === 'medium' ? 'bg-zinc-100 text-zinc-700 border border-zinc-200' : 'bg-zinc-50 text-zinc-500 border border-zinc-200') ?>">
                                        <?= e($t['priority']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border <?= $statusClasses[$t['status']] ?? 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                        <?= $statusLabels[$t['status']] ?? ucfirst($t['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-zinc-700">
                                    <?= (int)$t['message_count'] ?>
                                </td>
                                <td class="py-3.5 px-4 text-right text-zinc-400 whitespace-nowrap">
                                    <?= date('M d, H:i', strtotime($t['updated_at'])) ?>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <a href="/tickets/<?= (int)$t['id'] ?>" class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-lg transition-colors inline-block">
                                        View Thread &rarr;
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
