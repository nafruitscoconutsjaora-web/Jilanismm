<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Notifications</h1>
            <p class="mt-1 text-sm text-zinc-500">System announcements, order status alerts, and wallet notices.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <form action="/notifications/read-all" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50">
                    Mark All as Read
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-xs overflow-hidden divide-y divide-zinc-100">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="p-5 flex items-start justify-between <?= $notif['is_read'] ? 'bg-white' : 'bg-rose-50/40' ?> transition-colors">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <?php if (!$notif['is_read']): ?>
                                <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                            <?php endif; ?>
                            <h3 class="text-sm font-bold text-zinc-900"><?= e($notif['title']) ?></h3>
                            <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] uppercase font-semibold text-zinc-500"><?= e($notif['type']) ?></span>
                        </div>
                        <p class="text-xs text-zinc-600 leading-relaxed pl-4"><?= e($notif['message']) ?></p>
                        <div class="text-[10px] text-zinc-400 pl-4 pt-1"><?= date('M j, Y H:i', strtotime($notif['created_at'])) ?></div>
                    </div>

                    <?php if (!$notif['is_read']): ?>
                        <form action="/notifications/read/<?= e($notif['id']) ?>" method="POST" class="ml-4 flex-shrink-0">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-medium">
                                Mark read
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-12 text-center text-xs text-zinc-400">
                You have no notifications yet.
            </div>
        <?php endif; ?>
    </div>
</div>
