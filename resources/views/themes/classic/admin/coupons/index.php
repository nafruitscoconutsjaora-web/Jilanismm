<?php
/** @var array $coupons */
/** @var array $filters */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Discount Coupons</h1>
            <p class="text-sm text-zinc-500 mt-1">Create and manage promo codes, percentage or fixed discounts, usage caps, and expiration dates.</p>
        </div>
        <button type="button" onclick="openCreateCouponModal()" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Create New Coupon
        </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="/admin/coupons" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-zinc-500 uppercase tracking-wider mb-1">Search Code</label>
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="E.g. SUMMER20, VIP50" class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-zinc-500 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3.5 py-2 text-xs text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                <a href="/admin/coupons" class="px-3 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-xs font-semibold rounded-xl transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50/70 text-[11px] uppercase tracking-wider text-zinc-500 font-semibold">
                        <th class="py-3 px-5">Code</th>
                        <th class="py-3 px-4">Discount</th>
                        <th class="py-3 px-4">Min Order</th>
                        <th class="py-3 px-4 text-center">Usage Limit</th>
                        <th class="py-3 px-4 text-center">Used Count</th>
                        <th class="py-3 px-4">Total Discount Given</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 font-medium text-zinc-700">
                    <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="9" class="py-8 text-center text-zinc-400">No discount coupons found. Click "Create New Coupon" to add one.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $c): ?>
                            <?php
                            $isExpired = !empty($c['expires_at']) && strtotime($c['expires_at']) < time();
                            ?>
                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                <td class="py-3.5 px-5">
                                    <div class="font-mono font-bold text-zinc-900 text-sm tracking-wide bg-zinc-100 px-2.5 py-1 rounded-lg inline-block border border-zinc-200">
                                        <?= e($c['code']) ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-zinc-900">
                                        <?php if ($c['type'] === 'percentage'): ?>
                                            <?= number_format((float)$c['amount'], 1) ?>% OFF
                                            <?php if (!empty($c['max_discount']) && (float)$c['max_discount'] > 0): ?>
                                                <span class="block text-[10px] text-zinc-400 font-normal">Cap: $<?= number_format((float)$c['max_discount'], 2) ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            $<?= number_format((float)$c['amount'], 2) ?> Fixed
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-zinc-600">
                                    $<?= number_format((float)$c['min_order_amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="text-zinc-700 font-semibold">
                                        <?= (int)$c['max_uses'] > 0 ? number_format((int)$c['max_uses']) : 'Unlimited' ?>
                                    </span>
                                    <span class="block text-[10px] text-zinc-400">
                                        <?= (int)$c['per_user_limit'] ?>/user
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-800 font-bold text-[11px]">
                                        <?= number_format((int)$c['used_count']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-emerald-600">
                                    $<?= number_format((float)($c['total_discount_given'] ?? 0), 4) ?>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-500 whitespace-nowrap">
                                    <?php if (empty($c['expires_at'])): ?>
                                        <span class="text-zinc-400">Never</span>
                                    <?php else: ?>
                                        <span class="<?= $isExpired ? 'text-rose-600 font-semibold' : '' ?>">
                                            <?= date('M d, Y', strtotime($c['expires_at'])) ?>
                                            <?= $isExpired ? '(Expired)' : '' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border <?= $c['status'] === 'active' && !$isExpired ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200' ?>">
                                        <?= $isExpired ? 'Expired' : ucfirst($c['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap space-x-1.5">
                                    <a href="/admin/coupons/usage/<?= (int)$c['id'] ?>" class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-lg transition-colors inline-block">
                                        Logs
                                    </a>

                                    <button type="button" onclick='openEditCouponModal(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-lg transition-colors">
                                        Edit
                                    </button>

                                    <form action="/admin/coupons/toggle/<?= (int)$c['id'] ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2 py-1 text-xs font-semibold rounded-lg border <?= $c['status'] === 'active' ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' ?> transition-colors">
                                            <?= $c['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>

                                    <form action="/admin/coupons/delete/<?= (int)$c['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this coupon? If it has usage history it will be safely deactivated.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Coupon -->
<div id="createCouponModal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
                <h3 class="text-lg font-bold text-zinc-900">Create Discount Coupon</h3>
                <button type="button" onclick="closeCreateCouponModal()" class="text-zinc-400 hover:text-zinc-600">&times;</button>
            </div>
            <form action="/admin/coupons/create" method="POST" class="mt-4 space-y-4">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Coupon Code *</label>
                        <input type="text" name="code" required placeholder="E.g. PROMO20" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs font-mono uppercase focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Discount Type</label>
                        <select name="type" id="create_coupon_type" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <option value="fixed">Fixed Amount ($)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Discount Value *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" required placeholder="10.00" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Min Order Amount ($)</label>
                        <input type="number" step="0.01" min="0" name="min_order_amount" value="0.00" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Max Discount Cap ($ optional)</label>
                        <input type="number" step="0.01" min="0" name="max_discount" placeholder="Optional" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Per-User Limit</label>
                        <input type="number" min="1" step="1" name="per_user_limit" value="1" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Total Usage Limit (0 = unl.)</label>
                        <input type="number" min="0" step="1" name="max_uses" value="0" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Status</label>
                        <select name="status" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Start Date (optional)</label>
                        <input type="datetime-local" name="start_date" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Expiry Date (optional)</label>
                        <input type="datetime-local" name="expires_at" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-200 flex justify-end space-x-2">
                    <button type="button" onclick="closeCreateCouponModal()" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl">Create Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Coupon -->
<div id="editCouponModal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
                <h3 class="text-lg font-bold text-zinc-900">Edit Coupon</h3>
                <button type="button" onclick="closeEditCouponModal()" class="text-zinc-400 hover:text-zinc-600">&times;</button>
            </div>
            <form id="editCouponForm" action="" method="POST" class="mt-4 space-y-4">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Coupon Code *</label>
                        <input type="text" id="edit_code" name="code" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs font-mono uppercase focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Discount Type</label>
                        <select name="type" id="edit_type" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <option value="fixed">Fixed Amount ($)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Discount Value *</label>
                        <input type="number" step="0.01" min="0.01" id="edit_amount" name="amount" required class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Min Order Amount ($)</label>
                        <input type="number" step="0.01" min="0" id="edit_min_order" name="min_order_amount" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Max Discount Cap ($)</label>
                        <input type="number" step="0.01" min="0" id="edit_max_discount" name="max_discount" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Per-User Limit</label>
                        <input type="number" min="1" step="1" id="edit_per_user_limit" name="per_user_limit" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Total Usage Limit</label>
                        <input type="number" min="0" step="1" id="edit_max_uses" name="max_uses" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Status</label>
                        <select name="status" id="edit_status" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Start Date</label>
                        <input type="datetime-local" id="edit_start_date" name="start_date" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Expiry Date</label>
                        <input type="datetime-local" id="edit_expires_at" name="expires_at" class="w-full rounded-xl border border-zinc-300 px-3 py-2 text-xs focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-200 flex justify-end space-x-2">
                    <button type="button" onclick="closeEditCouponModal()" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateCouponModal() {
    document.getElementById('createCouponModal').classList.remove('hidden');
}
function closeCreateCouponModal() {
    document.getElementById('createCouponModal').classList.add('hidden');
}

function openEditCouponModal(coupon) {
    const form = document.getElementById('editCouponForm');
    form.action = '/admin/coupons/update/' + coupon.id;
    document.getElementById('edit_code').value = coupon.code;
    document.getElementById('edit_type').value = coupon.type;
    document.getElementById('edit_amount').value = parseFloat(coupon.amount);
    document.getElementById('edit_min_order').value = parseFloat(coupon.min_order_amount || 0);
    document.getElementById('edit_max_discount').value = coupon.max_discount ? parseFloat(coupon.max_discount) : '';
    document.getElementById('edit_per_user_limit').value = parseInt(coupon.per_user_limit || 1);
    document.getElementById('edit_max_uses').value = parseInt(coupon.max_uses || 0);
    document.getElementById('edit_status').value = coupon.status;

    if (coupon.start_date) {
        document.getElementById('edit_start_date').value = coupon.start_date.substring(0, 16);
    } else {
        document.getElementById('edit_start_date').value = '';
    }

    if (coupon.expires_at) {
        document.getElementById('edit_expires_at').value = coupon.expires_at.substring(0, 16);
    } else {
        document.getElementById('edit_expires_at').value = '';
    }

    document.getElementById('editCouponModal').classList.remove('hidden');
}

function closeEditCouponModal() {
    document.getElementById('editCouponModal').classList.add('hidden');
}
</script>
