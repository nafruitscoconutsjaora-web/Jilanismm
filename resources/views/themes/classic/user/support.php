<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Support Center</h1>
            <p class="mt-1 text-sm text-zinc-500">Submit tickets for order adjustments, speed inquiries, or technical support.</p>
        </div>
    </div>

    <!-- Official Channel Notice -->
    <div class="rounded-2xl border border-rose-100 bg-rose-50 p-6">
        <h3 class="text-xs font-bold uppercase tracking-wider text-rose-700">Direct Support Hotline</h3>
        <p class="mt-1 text-sm text-zinc-800">Email: <strong class="text-rose-950 font-mono"><?= e(setting('support_email', 'support@smmpanel.local')) ?></strong></p>
        <p class="mt-2 text-xs text-rose-700">
            Support tickets and automatic order resends will be interactive in Part 2.
        </p>
    </div>

    <!-- Tickets History -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-zinc-900">My Tickets</h2>
        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-xs overflow-hidden">
            <?php if (!empty($tickets)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                        <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3.5">ID</th>
                                <th class="px-5 py-3.5">Subject</th>
                                <th class="px-5 py-3.5">Priority</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <?php foreach ($tickets as $t): ?>
                                <tr class="hover:bg-zinc-50/80">
                                    <td class="px-5 py-3.5 font-mono text-xs text-zinc-400">#<?= e($t['id']) ?></td>
                                    <td class="px-5 py-3.5 font-semibold text-zinc-900"><?= e($t['subject']) ?></td>
                                    <td class="px-5 py-3.5 text-xs uppercase font-semibold text-zinc-600"><?= e($t['priority']) ?></td>
                                    <td class="px-5 py-3.5">
                                        <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 capitalize">
                                            <?= e($t['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-zinc-400"><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-12 text-center text-xs text-zinc-400">
                    No active or past support tickets on record.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
