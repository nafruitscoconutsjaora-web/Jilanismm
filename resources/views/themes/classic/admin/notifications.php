<div class="space-y-8 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Broadcast Notifications</h1>
        <p class="mt-1 text-xs text-zinc-500">Dispatch system alerts, rate changes, or network status advisories to users.</p>
    </div>

    <!-- Dispatch Form -->
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8 shadow-xs">
        <h2 class="text-base font-bold text-zinc-900 mb-4">Send New Notification</h2>
        <form action="/admin/notifications" method="POST" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label for="title" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Title / Headline</label>
                <input type="text" id="title" name="title" required placeholder="e.g. Instagram API Node Upgrade Completed" 
                       class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Notice Type</label>
                    <select id="type" name="type" class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                        <option value="info">Informational (Blue)</option>
                        <option value="success">Success / Milestone (Green)</option>
                        <option value="warning">Warning / Service Alert (Amber)</option>
                    </select>
                </div>

                <div>
                    <label for="user_id" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Target User ID <span class="text-zinc-400 font-normal lowercase">(leave blank for all users)</span></label>
                    <input type="number" id="user_id" name="user_id" placeholder="All Active Clients" 
                           class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label for="message" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Notification Content</label>
                <textarea id="message" name="message" rows="3" required placeholder="Describe the announcement, change details, or operational notes..." 
                          class="mt-1 block w-full rounded-xl border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 focus:border-rose-500 focus:outline-none"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="rounded-xl bg-rose-600 px-5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition-colors">
                    Dispatch Notification &rarr;
                </button>
            </div>
        </form>
    </div>

    <!-- Past Notices -->
    <div class="space-y-4">
        <h2 class="text-base font-bold text-zinc-900">Recent Dispatches</h2>
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs overflow-hidden divide-y divide-zinc-100">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="p-5 flex items-start justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-sm font-bold text-zinc-900"><?= e($n['title']) ?></h3>
                                <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] uppercase font-semibold text-zinc-600"><?= e($n['type']) ?></span>
                                <span class="text-xs text-zinc-400">User: <?= $n['user_id'] ? '#' . e($n['user_id']) : '<strong class="text-zinc-600">Broadcast (All)</strong>' ?></span>
                            </div>
                            <p class="text-xs text-zinc-600 leading-relaxed"><?= e($n['message']) ?></p>
                            <div class="text-[10px] text-zinc-400 pt-1"><?= date('M j, Y H:i', strtotime($n['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-12 text-center text-xs text-zinc-400">
                    No broadcast notices dispatched yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
