<?php
/** @var array $user */
/** @var array $categories */
/** @var int|null $preselectedOrderId */
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-2 text-xs text-zinc-500 pb-2">
        <a href="/tickets" class="text-rose-600 hover:underline font-semibold">&larr; Back to Support Tickets</a>
        <span class="text-zinc-300">/</span>
        <span>Open New Ticket</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Ticket Form -->
        <div class="lg:col-span-2 bg-white border border-zinc-200/80 rounded-2xl p-6 sm:p-8 shadow-xs">
            <h1 class="text-xl font-bold tracking-tight text-zinc-900 mb-1">Submit a Support Ticket</h1>
            <p class="text-xs text-zinc-500 mb-6">Describe your issue in detail and our support specialists will help you resolve it.</p>

            <form action="/tickets/create" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label for="ticket_subject" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1.5">Subject *</label>
                    <input type="text" id="ticket_subject" name="subject" required value="<?= e(old('subject')) ?>" placeholder="E.g. Issue with Instagram Followers order delivery" class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 px-3.5 py-2.5 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="ticket_category" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1.5">Category *</label>
                        <select id="ticket_category" name="category" required class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 px-3.5 py-2.5 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= old('category') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="ticket_priority" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1.5">Priority</label>
                        <select id="ticket_priority" name="priority" class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 px-3.5 py-2.5 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>Low - General Inquiry</option>
                            <option value="medium" <?= old('priority', 'medium') === 'medium' ? 'selected' : '' ?>>Medium - Standard Request</option>
                            <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>High - Urgent Order Problem</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="ticket_order_id" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1.5">Related Order ID <span class="text-zinc-400 font-normal lowercase">(optional)</span></label>
                    <input type="number" id="ticket_order_id" name="order_id" value="<?= (int)old('order_id', $preselectedOrderId ?? '') ?: '' ?>" placeholder="E.g. 1024" class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 px-3.5 py-2.5 text-xs text-zinc-900 font-mono focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    <p class="text-[11px] text-zinc-400 mt-1">If this ticket is about a specific order, please provide the ID so we can inspect provider logs immediately.</p>
                </div>

                <div>
                    <label for="ticket_message" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1.5">Message / Details *</label>
                    <textarea id="ticket_message" name="message" required rows="6" placeholder="Provide full details: order link, what happened, expected delivery, error messages..." class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 p-3.5 text-xs text-zinc-900 font-medium leading-relaxed focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20"><?= e(old('message')) ?></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end space-x-3">
                    <a href="/tickets" class="px-4 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-xl transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                        Submit Ticket &rarr;
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Tips Panel -->
        <div class="space-y-4">
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-zinc-900 pb-2 border-b border-zinc-100">Ticket Guidelines</h3>
                <ul class="space-y-3 text-xs text-zinc-600 leading-relaxed">
                    <li class="flex items-start space-x-2">
                        <span class="text-rose-600 font-bold">•</span>
                        <span><strong>One issue per ticket:</strong> Opening separate tickets for distinct issues ensures faster resolution.</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <span class="text-rose-600 font-bold">•</span>
                        <span><strong>Include Order ID:</strong> For refill, speed, or drop inquiries, linking the exact Order ID speeds up investigation.</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <span class="text-rose-600 font-bold">•</span>
                        <span><strong>Check Refill Window:</strong> Verify that your service supports refills and that the order is within the refill guarantee period.</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <span class="text-rose-600 font-bold">•</span>
                        <span><strong>Response Time:</strong> Normal turnaround is under 2 hours, with a maximum of 24 hours during peak times.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
