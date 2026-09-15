<?php
/** @var array $user */
/** @var array $wallet */
/** @var array $categories */
/** @var array $services */
/** @var int $preselectedServiceId */
?>

<div class="max-w-5xl mx-auto space-y-8">
    <!-- Top Header & Balance Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-zinc-200">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Place New Order</h1>
            <p class="text-sm text-zinc-500 mt-1">Select a service, enter your target URL or username, and launch your campaign instantly.</p>
        </div>
        <div class="flex items-center space-x-3 bg-white border border-zinc-200 rounded-2xl px-4 py-2.5 shadow-xs">
            <div class="h-9 w-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-base">
                $
            </div>
            <div>
                <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-semibold">Wallet Balance</div>
                <div class="text-base font-bold text-zinc-900">
                    $<?= number_format((float)($wallet['balance'] ?? 0), 4) ?> <span class="text-xs font-medium text-zinc-400">USD</span>
                </div>
            </div>
            <a href="/wallet/add-funds" class="ml-2 inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                + Add Funds
            </a>
        </div>
    </div>

    <!-- Main Order Form Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Order Form -->
        <div class="lg:col-span-2 bg-white border border-zinc-200/80 rounded-2xl p-6 sm:p-8 shadow-xs">
            <form id="orderForm" action="/order/new" method="POST" class="space-y-6">
                <?= csrf_field() ?>

                <!-- Category Selection -->
                <div>
                    <label for="category_select" class="block text-sm font-semibold text-zinc-800 mb-2">Category</label>
                    <div class="relative">
                        <select id="category_select" class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 px-4 py-3 text-sm text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?> (<?= (int)$cat['service_count'] ?> services)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Service Selection -->
                <div>
                    <label for="service_select" class="block text-sm font-semibold text-zinc-800 mb-2">Service</label>
                    <div class="relative">
                        <select id="service_select" name="service_id" required class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 px-4 py-3 text-sm text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">-- First select a category above --</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Target Link -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="order_link" class="block text-sm font-semibold text-zinc-800">Target Link or Username</label>
                        <span class="text-xs text-zinc-400" id="linkHint">E.g. https://instagram.com/p/...</span>
                    </div>
                    <input type="text" id="order_link" name="link" required placeholder="https://..." class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 px-4 py-3 text-sm text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all">
                </div>

                <!-- Quantity & Charge Preview Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="order_quantity" class="block text-sm font-semibold text-zinc-800">Quantity</label>
                            <span class="text-xs font-medium text-zinc-500" id="qtyLimitsBadge">Min: - | Max: -</span>
                        </div>
                        <input type="number" id="order_quantity" name="quantity" required min="1" step="1" placeholder="1000" class="w-full rounded-xl border border-zinc-300 bg-zinc-50/50 px-4 py-3 text-sm text-zinc-900 font-medium focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-800 mb-2">Calculated Subtotal</label>
                        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-100/70 px-4 py-3 text-zinc-900 font-bold text-base">
                            <span id="calculatedChargeDisplay">$0.0000</span>
                            <span class="text-xs uppercase font-semibold text-zinc-500">USD</span>
                        </div>
                    </div>
                </div>

                <!-- Coupon Code Section -->
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 space-y-3">
                    <label for="coupon_code" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Have a Discount Coupon?</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="coupon_code" name="coupon_code" placeholder="Enter coupon code (e.g. SAVE20)" class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-xs text-zinc-900 font-mono uppercase focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        <button type="button" id="applyCouponBtn" class="px-4 py-2.5 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-semibold rounded-xl whitespace-nowrap transition-colors">
                            Apply
                        </button>
                    </div>
                    <div id="couponFeedback" class="hidden text-xs font-medium"></div>
                    <div id="couponDiscountRow" class="hidden flex items-center justify-between text-xs pt-2 border-t border-zinc-200">
                        <span class="text-emerald-700 font-semibold flex items-center">
                            <svg class="h-3.5 w-3.5 mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Coupon Discount:
                        </span>
                        <span id="discountValueDisplay" class="font-mono font-bold text-emerald-600">-$0.0000</span>
                    </div>
                    <div id="finalPayableRow" class="hidden flex items-center justify-between text-xs font-bold text-zinc-900 pt-1">
                        <span>Final Payable Amount:</span>
                        <span id="finalPayableDisplay" class="font-mono text-sm text-rose-600">$0.0000</span>
                    </div>
                </div>

                <!-- Insufficient Funds Warning (Conditional) -->
                <div id="fundsWarning" class="hidden rounded-xl border border-amber-300 bg-amber-50 p-4 text-xs text-amber-800">
                    <div class="flex items-center space-x-2">
                        <svg class="h-4 w-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Your wallet balance is insufficient for this order amount. <a href="/wallet/add-funds" class="font-bold underline ml-1 hover:text-amber-900">Add funds now &rarr;</a></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn" class="w-full flex items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold py-3.5 px-6 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                    Submit Order
                </button>
            </form>
        </div>

        <!-- Right: Live Service Details Card -->
        <div class="space-y-6">
            <div id="serviceDetailsCard" class="bg-white border border-zinc-200/80 rounded-2xl p-6 shadow-xs">
                <h3 class="text-base font-bold text-zinc-900 pb-3 border-b border-zinc-100 flex items-center justify-between">
                    <span>Service Information</span>
                    <span id="serviceBadge" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-600">No Service</span>
                </h3>

                <div class="mt-4 space-y-4 text-sm">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-zinc-400 font-semibold">Service Name</div>
                        <div id="infoName" class="font-medium text-zinc-800 mt-0.5">Please select a service from the form</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="bg-zinc-50 p-3 rounded-xl border border-zinc-100">
                            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-medium">Rate / 1000</div>
                            <div id="infoRate" class="text-base font-bold text-rose-600 mt-0.5">$0.0000</div>
                        </div>
                        <div class="bg-zinc-50 p-3 rounded-xl border border-zinc-100">
                            <div class="text-[11px] uppercase tracking-wider text-zinc-400 font-medium">Min / Max Qty</div>
                            <div id="infoLimits" class="text-xs font-bold text-zinc-700 mt-1">- / -</div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="text-xs uppercase tracking-wider text-zinc-400 font-semibold mb-1">Description & Guidelines</div>
                        <div id="infoDescription" class="text-xs text-zinc-600 leading-relaxed bg-zinc-50 p-3.5 rounded-xl border border-zinc-100 whitespace-pre-line min-h-[90px]">
                            Select a service to view full specifications, start time estimates, and refill policies.
                        </div>
                    </div>

                    <!-- Badges -->
                    <div id="infoFeatures" class="pt-2 flex flex-wrap gap-2">
                        <!-- Refill / Cancel Badges dynamically populated -->
                    </div>
                </div>
            </div>

            <!-- Guarantee Box -->
            <div class="bg-gradient-to-br from-zinc-900 to-zinc-800 text-white rounded-2xl p-5 shadow-xs">
                <div class="flex items-center space-x-2 text-rose-400 text-xs font-bold uppercase tracking-wider mb-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Instant Execution Engine</span>
                </div>
                <p class="text-xs text-zinc-300 leading-relaxed">
                    All orders are validated strictly on the server and routed securely. If an upstream provider is unavailable, your wallet funds are automatically refunded to your balance.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Raw Services JSON Data for dynamic filtering -->
<script>
const servicesData = <?= json_encode($services, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const userBalance = <?= (float)($wallet['balance'] ?? 0) ?>;
const preselectedServiceId = <?= (int)$preselectedServiceId ?>;

document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.getElementById('category_select');
    const serviceSelect = document.getElementById('service_select');
    const quantityInput = document.getElementById('order_quantity');
    const chargeDisplay = document.getElementById('calculatedChargeDisplay');
    const fundsWarning = document.getElementById('fundsWarning');

    const infoName = document.getElementById('infoName');
    const infoRate = document.getElementById('infoRate');
    const infoLimits = document.getElementById('infoLimits');
    const infoDescription = document.getElementById('infoDescription');
    const serviceBadge = document.getElementById('serviceBadge');
    const qtyLimitsBadge = document.getElementById('qtyLimitsBadge');
    const infoFeatures = document.getElementById('infoFeatures');

    let currentSelectedService = null;

    // Filter services when category changes
    function populateServicesForCategory(categoryId, selectServiceId = null) {
        serviceSelect.innerHTML = '<option value="">-- Choose Service --</option>';
        if (!categoryId) return;

        const filtered = servicesData.filter(s => parseInt(s.category_id) === parseInt(categoryId));
        filtered.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            const price = parseFloat(s.customer_price > 0 ? s.customer_price : s.price_per_k);
            opt.textContent = `${s.id} - ${s.name} ($${price.toFixed(4)} / 1k)`;
            if (selectServiceId && parseInt(s.id) === parseInt(selectServiceId)) {
                opt.selected = true;
            }
            serviceSelect.appendChild(opt);
        });

        if (selectServiceId && filtered.some(s => parseInt(s.id) === parseInt(selectServiceId))) {
            updateSelectedService(selectServiceId);
        } else if (filtered.length > 0) {
            updateSelectedService(filtered[0].id);
            serviceSelect.value = filtered[0].id;
        } else {
            updateSelectedService(null);
        }
    }

    categorySelect.addEventListener('change', () => {
        populateServicesForCategory(categorySelect.value);
    });

    serviceSelect.addEventListener('change', () => {
        updateSelectedService(serviceSelect.value);
    });

    function updateSelectedService(serviceId) {
        if (!serviceId) {
            currentSelectedService = null;
            infoName.textContent = 'Please select a service';
            infoRate.textContent = '$0.0000';
            infoLimits.textContent = '- / -';
            infoDescription.textContent = 'Select a service to view full specifications.';
            serviceBadge.textContent = 'No Service';
            qtyLimitsBadge.textContent = 'Min: - | Max: -';
            infoFeatures.innerHTML = '';
            calculateCharge();
            return;
        }

        currentSelectedService = servicesData.find(s => parseInt(s.id) === parseInt(serviceId));
        if (!currentSelectedService) return;

        const price = parseFloat(currentSelectedService.customer_price > 0 ? currentSelectedService.customer_price : currentSelectedService.price_per_k);
        const minQty = parseInt(currentSelectedService.min_quantity);
        const maxQty = parseInt(currentSelectedService.max_quantity);

        infoName.textContent = currentSelectedService.name;
        infoRate.textContent = `$${price.toFixed(4)}`;
        infoLimits.textContent = `${minQty.toLocaleString()} / ${maxQty.toLocaleString()}`;
        infoDescription.textContent = currentSelectedService.description || 'No specific instructions provided for this service.';
        serviceBadge.textContent = `ID #${currentSelectedService.id}`;
        qtyLimitsBadge.textContent = `Min: ${minQty.toLocaleString()} | Max: ${maxQty.toLocaleString()}`;

        quantityInput.min = minQty;
        quantityInput.max = maxQty;
        if (!quantityInput.value || parseInt(quantityInput.value) < minQty) {
            quantityInput.value = minQty;
        }

        // Features badges
        infoFeatures.innerHTML = '';
        if (currentSelectedService.refill) {
            infoFeatures.innerHTML += '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Refill Button Supported</span>';
        }
        if (currentSelectedService.dripfeed) {
            infoFeatures.innerHTML += '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Drip-Feed Enabled</span>';
        }

        calculateCharge();
    }

    const couponInput = document.getElementById('coupon_code');
    const applyCouponBtn = document.getElementById('applyCouponBtn');
    const couponFeedback = document.getElementById('couponFeedback');
    const couponDiscountRow = document.getElementById('couponDiscountRow');
    const discountValueDisplay = document.getElementById('discountValueDisplay');
    const finalPayableRow = document.getElementById('finalPayableRow');
    const finalPayableDisplay = document.getElementById('finalPayableDisplay');

    let appliedCoupon = null;

    // Dynamic Charge Calculation
    function calculateCharge() {
        if (!currentSelectedService) {
            chargeDisplay.textContent = '$0.0000';
            fundsWarning.classList.add('hidden');
            resetCouponDisplay();
            return;
        }

        const qty = parseInt(quantityInput.value) || 0;
        const price = parseFloat(currentSelectedService.customer_price > 0 ? currentSelectedService.customer_price : currentSelectedService.price_per_k);

        const subtotal = (qty / 1000.0) * price;
        chargeDisplay.textContent = `$${subtotal.toFixed(4)}`;

        let finalCharge = subtotal;

        if (appliedCoupon && appliedCoupon.valid) {
            let discount = 0;
            if (appliedCoupon.type === 'percentage') {
                discount = (subtotal * parseFloat(appliedCoupon.amount)) / 100.0;
                if (appliedCoupon.max_discount && parseFloat(appliedCoupon.max_discount) > 0) {
                    discount = Math.min(discount, parseFloat(appliedCoupon.max_discount));
                }
            } else {
                discount = parseFloat(appliedCoupon.amount);
            }
            discount = Math.min(discount, subtotal);
            finalCharge = Math.max(0, subtotal - discount);

            discountValueDisplay.textContent = `-$${discount.toFixed(4)}`;
            finalPayableDisplay.textContent = `$${finalCharge.toFixed(4)}`;
            couponDiscountRow.classList.remove('hidden');
            finalPayableRow.classList.remove('hidden');
        } else {
            couponDiscountRow.classList.add('hidden');
            finalPayableRow.classList.add('hidden');
        }

        if (finalCharge > userBalance) {
            fundsWarning.classList.remove('hidden');
        } else {
            fundsWarning.classList.add('hidden');
        }
    }

    function resetCouponDisplay() {
        appliedCoupon = null;
        couponDiscountRow.classList.add('hidden');
        finalPayableRow.classList.add('hidden');
        couponFeedback.classList.add('hidden');
    }

    applyCouponBtn.addEventListener('click', async () => {
        const code = couponInput.value.trim();
        if (!code) {
            showCouponFeedback('Please enter a coupon code', 'error');
            return;
        }
        if (!currentSelectedService) {
            showCouponFeedback('Please select a service first', 'error');
            return;
        }
        const qty = parseInt(quantityInput.value) || 0;
        if (qty <= 0) {
            showCouponFeedback('Please enter a valid quantity first', 'error');
            return;
        }

        applyCouponBtn.disabled = true;
        applyCouponBtn.textContent = 'Checking...';

        try {
            const res = await fetch('/api/coupon/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    code: code,
                    service_id: currentSelectedService.id,
                    quantity: qty
                })
            });

            const data = await res.json();
            if (data.valid) {
                appliedCoupon = {
                    valid: true,
                    code: data.code,
                    type: data.type,
                    amount: data.type === 'percentage' ? ((data.discount / data.original_amount) * 100) : data.discount,
                    max_discount: 0
                };
                showCouponFeedback(data.message, 'success');
                calculateCharge();
            } else {
                appliedCoupon = null;
                showCouponFeedback(data.error || 'Invalid coupon code', 'error');
                calculateCharge();
            }
        } catch (err) {
            showCouponFeedback('Failed to validate coupon: ' + err.message, 'error');
        } finally {
            applyCouponBtn.disabled = false;
            applyCouponBtn.textContent = 'Apply';
        }
    });

    function showCouponFeedback(msg, type) {
        couponFeedback.textContent = msg;
        couponFeedback.classList.remove('hidden', 'text-emerald-600', 'text-rose-600');
        if (type === 'success') {
            couponFeedback.classList.add('text-emerald-600');
        } else {
            couponFeedback.classList.add('text-rose-600');
        }
    }

    quantityInput.addEventListener('input', calculateCharge);

    // Initial preselection if provided via query (?service_id=X)
    if (preselectedServiceId > 0) {
        const found = servicesData.find(s => parseInt(s.id) === preselectedServiceId);
        if (found) {
            categorySelect.value = found.category_id;
            populateServicesForCategory(found.category_id, found.id);
        }
    } else if (categorySelect.options.length > 1) {
        categorySelect.selectedIndex = 1;
        populateServicesForCategory(categorySelect.value);
    }
});
</script>
