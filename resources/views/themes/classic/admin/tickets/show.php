<?php
/** @var array $ticket */
/** @var array $admins */
/** @var array $userRecentOrders */
?>

<div class="space-y-6">
    <!-- Breadcrumb & Status Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-200">
        <a href="/admin/tickets" class="text-xs text-rose-600 hover:underline font-semibold flex items-center">
            &larr; Back to Ticket Desk
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Change Status Form -->
            <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/status" method="POST" class="flex items-center space-x-2">
                <?= csrf_field() ?>
                <label class="text-xs text-zinc-500 font-semibold">Status:</label>
                <select name="status" onchange="this.form.submit()" class="rounded-xl border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-900 font-bold focus:border-rose-500 focus:outline-none">
                    <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="customer_reply" <?= $ticket['status'] === 'customer_reply' ? 'selected' : '' ?>>Customer Reply</option>
                    <option value="answered" <?= $ticket['status'] === 'answered' ? 'selected' : '' ?>>Answered</option>
                    <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </form>

            <!-- Assign Staff Form -->
            <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/assign" method="POST" class="flex items-center space-x-2">
                <?= csrf_field() ?>
                <label class="text-xs text-zinc-500 font-semibold">Assignee:</label>
                <select name="admin_id" onchange="this.form.submit()" class="rounded-xl border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:outline-none">
                    <option value="0">-- Unassigned --</option>
                    <?php foreach ($admins as $adm): ?>
                        <option value="<?= (int)$adm['id'] ?>" <?= (int)$ticket['assigned_to'] === (int)$adm['id'] ? 'selected' : '' ?>>
                            <?= e($adm['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Main Grid: Conversation + Sidebar Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Conversation Thread -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Info Card -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center space-x-2 text-xs text-zinc-400 font-mono">
                    <span>Ticket #<?= (int)$ticket['id'] ?></span>
                    <span>•</span>
                    <span class="font-semibold text-zinc-700"><?= e($ticket['category']) ?></span>
                    <span>•</span>
                    <span class="uppercase font-bold <?= $ticket['priority'] === 'high' ? 'text-rose-600' : 'text-zinc-600' ?>"><?= e($ticket['priority']) ?> Priority</span>
                </div>
                <h1 class="text-xl font-bold text-zinc-900 mt-2"><?= e($ticket['subject']) ?></h1>
                <?php if (!empty($ticket['order_id'])): ?>
                    <div class="mt-3 p-3 bg-rose-50/60 border border-rose-200/80 rounded-xl text-xs flex items-center justify-between">
                        <span class="text-zinc-700">Referenced Order: <strong>#<?= (int)$ticket['order_id'] ?></strong></span>
                        <a href="/admin/orders?search=<?= (int)$ticket['order_id'] ?>" target="_blank" class="text-rose-600 hover:underline font-bold">
                            View Order Details &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Messages Stream -->
            <div class="space-y-4">
                <?php foreach ($ticket['messages'] as $msg): ?>
                    <?php $isAdmin = ($msg['sender_type'] === 'admin'); ?>
                    <div class="rounded-2xl p-6 shadow-xs border <?= $isAdmin ? 'bg-zinc-900 text-white border-zinc-800' : 'bg-white border-zinc-200/80' ?>">
                        <div class="flex items-center justify-between pb-3 border-b <?= $isAdmin ? 'border-zinc-800' : 'border-zinc-100' ?>">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center font-bold text-xs <?= $isAdmin ? 'bg-rose-600 text-white' : 'bg-zinc-100 text-zinc-800' ?>">
                                    <?= $isAdmin ? 'AD' : strtoupper(substr($msg['sender_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="text-xs font-bold <?= $isAdmin ? 'text-zinc-100' : 'text-zinc-900' ?>">
                                        <?= e($msg['sender_name']) ?>
                                        <?php if ($isAdmin): ?>
                                            <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-600 text-white">Staff Member</span>
                                        <?php else: ?>
                                            <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-100 text-zinc-600">Customer</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-[11px] <?= $isAdmin ? 'text-zinc-400' : 'text-zinc-400' ?> font-medium">
                                <?= date('M d, Y \a\t H:i', strtotime($msg['created_at'])) ?>
                            </div>
                        </div>
                        <div class="pt-4 text-xs sm:text-sm leading-relaxed whitespace-pre-line <?= $isAdmin ? 'text-zinc-200 font-normal' : 'text-zinc-800 font-normal' ?>">
                            <?= e($msg['message']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Reply Box -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
                <h3 class="text-sm font-bold text-zinc-900 mb-2">Staff Response</h3>
                <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/reply" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <div>
                        <textarea name="message" required rows="5" placeholder="Write staff response to customer..." class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 p-3.5 text-xs sm:text-sm text-zinc-900 font-medium leading-relaxed focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20"></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] text-zinc-400">Replying automatically changes status to <strong>Answered</strong> and notifies customer.</span>
                        <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                            Send Reply to User &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar: Customer Context -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-zinc-900 pb-2 border-b border-zinc-100">Customer Profile</h3>
                <div>
                    <div class="text-base font-bold text-zinc-900"><?= e($ticket['user_name']) ?></div>
                    <div class="text-xs text-zinc-500 font-mono"><?= e($ticket['user_email']) ?></div>
                </div>
                <div class="pt-2">
                    <a href="/admin/users/<?= (int)$ticket['user_id'] ?>" class="text-xs text-rose-600 font-semibold hover:underline">
                        View Full User Account &rarr;
                    </a>
                </div>
            </div>

            <!-- Customer's Recent Orders -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-zinc-900 pb-2 border-b border-zinc-100">Recent Customer Orders</h3>
                <?php if (empty($userRecentOrders)): ?>
                    <p class="text-xs text-zinc-400">No orders placed by this customer yet.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($userRecentOrders as $o): ?>
                            <div class="p-3 rounded-xl border border-zinc-100 bg-zinc-50/60 text-xs">
                                <div class="flex items-center justify-between font-bold text-zinc-900">
                                    <span>#<?= (int)$o['id'] ?></span>
                                    <span class="font-mono text-rose-600">$<?= number_format((float)$o['charge'], 2) ?></span>
                                </div>
                                <div class="text-zinc-600 truncate mt-1"><?= e($o['service_name']) ?></div>
                                <div class="flex items-center justify-between text-[10px] text-zinc-400 mt-1">
                                    <span>Qty: <?= number_format((int)$o['quantity']) ?></span>
                                    <span class="uppercase font-semibold text-zinc-600"><?= e($o['status']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
