<?php
/** @var array $user */
/** @var array $ticket */
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between pb-2">
        <a href="/tickets" class="text-xs text-rose-600 hover:underline font-semibold flex items-center">
            &larr; Back to All Tickets
        </a>
        <div class="flex items-center space-x-2">
            <?php if ($ticket['status'] !== 'closed'): ?>
                <form action="/tickets/<?= (int)$ticket['id'] ?>/close" method="POST" onsubmit="return confirm('Are you sure you want to mark this ticket as closed?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-lg transition-colors">
                        Mark as Closed
                    </button>
                </form>
            <?php else: ?>
                <form action="/tickets/<?= (int)$ticket['id'] ?>/reopen" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-lg transition-colors">
                        Reopen Ticket
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ticket Summary Card -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-zinc-100">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-mono font-bold text-zinc-400">#<?= (int)$ticket['id'] ?></span>
                    <h1 class="text-lg sm:text-xl font-bold text-zinc-900"><?= e($ticket['subject']) ?></h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-zinc-500">
                    <span class="font-semibold text-zinc-700"><?= e($ticket['category']) ?></span>
                    <span>•</span>
                    <span>Opened on <?= date('M d, Y \a\t H:i', strtotime($ticket['created_at'])) ?></span>
                    <?php if (!empty($ticket['order_id'])): ?>
                        <span>•</span>
                        <a href="/orders/<?= (int)$ticket['order_id'] ?>" class="text-rose-600 font-semibold hover:underline">
                            Linked Order #<?= (int)$ticket['order_id'] ?> &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <?php
                $statusClasses = [
                    'open' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'answered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'customer_reply' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'closed' => 'bg-zinc-100 text-zinc-600 border-zinc-200'
                ];
                $statusLabels = [
                    'open' => 'Open (Pending Staff)',
                    'answered' => 'Answered by Support',
                    'customer_reply' => 'Waiting on Staff',
                    'closed' => 'Ticket Closed'
                ];
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border <?= $statusClasses[$ticket['status']] ?? 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                    <?= $statusLabels[$ticket['status']] ?? ucfirst($ticket['status']) ?>
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold uppercase <?= $ticket['priority'] === 'high' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-zinc-100 text-zinc-700' ?>">
                    <?= e($ticket['priority']) ?>
                </span>
            </div>
        </div>

        <?php if ($ticket['status'] === 'closed'): ?>
            <div class="mt-4 p-3.5 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-600 flex items-center justify-between">
                <span>This ticket is currently closed. If your problem persists, you can click "Reopen Ticket" above.</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Message Timeline -->
    <div class="space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 px-1">Conversation Thread</h3>

        <?php foreach ($ticket['messages'] as $msg): ?>
            <?php $isAdmin = ($msg['sender_type'] === 'admin'); ?>
            <div class="rounded-2xl p-5 sm:p-6 shadow-xs border <?= $isAdmin ? 'bg-rose-50/40 border-rose-200/80 ml-4 sm:ml-8' : 'bg-white border-zinc-200/80 mr-4 sm:mr-8' ?>">
                <div class="flex items-center justify-between pb-3 border-b <?= $isAdmin ? 'border-rose-100' : 'border-zinc-100' ?>">
                    <div class="flex items-center space-x-3">
                        <div class="h-8 w-8 rounded-full flex items-center justify-center font-bold text-xs <?= $isAdmin ? 'bg-rose-600 text-white shadow-xs' : 'bg-zinc-800 text-white' ?>">
                            <?= $isAdmin ? 'SP' : strtoupper(substr($msg['sender_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="text-xs font-bold <?= $isAdmin ? 'text-rose-900' : 'text-zinc-900' ?>">
                                <?= e($msg['sender_name']) ?>
                                <?php if ($isAdmin): ?>
                                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-600 text-white">Support Team</span>
                                <?php else: ?>
                                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-100 text-zinc-600">You</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-[11px] text-zinc-400 font-medium">
                        <?= date('M d, Y \a\t H:i', strtotime($msg['created_at'])) ?>
                    </div>
                </div>
                <div class="pt-4 text-xs sm:text-sm text-zinc-800 leading-relaxed whitespace-pre-line font-normal">
                    <?= e($msg['message']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply Form Box -->
    <?php if ($ticket['status'] !== 'closed'): ?>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
            <h3 class="text-sm font-bold text-zinc-900 mb-3">Send a Reply</h3>
            <form action="/tickets/<?= (int)$ticket['id'] ?>/reply" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <textarea name="message" required rows="4" placeholder="Write your reply here..." class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 p-3.5 text-xs sm:text-sm text-zinc-900 font-medium leading-relaxed focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20"></textarea>
                </div>
                <div class="flex items-center justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                        Submit Reply &rarr;
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
