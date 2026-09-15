<!-- Hero Section -->
<section class="relative overflow-hidden py-20 sm:py-28 bg-gradient-to-b from-white to-zinc-50 border-b border-zinc-200/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center space-x-2 rounded-full border border-rose-200 bg-rose-50 px-3.5 py-1 text-xs font-semibold text-rose-700 shadow-xs mb-8">
            <span class="inline-block h-2 w-2 rounded-full bg-rose-600 animate-pulse"></span>
            <span>Enterprise SMM Infrastructure &bull; Automated API Core</span>
        </div>
        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-6xl max-w-3xl mx-auto leading-tight sm:leading-none">
            Scale Your Social Reach with <span class="text-rose-600">Automated Precision</span>
        </h1>
        <p class="mt-6 text-lg text-zinc-600 max-w-2xl mx-auto leading-relaxed">
            <?= e($siteDesc) ?>. High-speed order routing, verified delivery protocols, and 24/7 automated management.
        </p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            <a href="/register" class="rounded-xl bg-rose-600 px-6 py-3.5 text-sm font-semibold text-white shadow-md hover:bg-rose-700 transition-all hover:shadow-lg">
                Create Free Account &rarr;
            </a>
            <a href="/services" class="rounded-xl border border-zinc-300 bg-white px-6 py-3.5 text-sm font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 transition-colors">
                Browse Service Directory
            </a>
        </div>

        <!-- Quick Platform Stats -->
        <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-4 max-w-4xl mx-auto border-t border-zinc-200/80 pt-10">
            <div class="p-4">
                <div class="text-3xl font-bold text-zinc-900"><?= $servicesCount > 0 ? number_format($servicesCount) : 'Active' ?></div>
                <div class="text-xs font-medium text-zinc-500 mt-1 uppercase tracking-wider">Services In Catalog</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-zinc-900">&lt; 60s</div>
                <div class="text-xs font-medium text-zinc-500 mt-1 uppercase tracking-wider">Average Dispatch</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-zinc-900">99.9%</div>
                <div class="text-xs font-medium text-zinc-500 mt-1 uppercase tracking-wider">System Availability</div>
            </div>
            <div class="p-4">
                <div class="text-3xl font-bold text-zinc-900">24 / 7</div>
                <div class="text-xs font-medium text-zinc-500 mt-1 uppercase tracking-wider">Automated Support</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600">Engineered For Efficiency</h2>
            <h3 class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">How The Platform Works</h3>
            <p class="mt-4 text-sm text-zinc-600">Get your campaigns deployed in three transparent steps.</p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200/80 p-8 shadow-xs hover:border-rose-200 transition-colors">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 font-bold text-lg">
                    1
                </div>
                <h4 class="mt-6 text-lg font-bold text-zinc-900">Register & Deposit</h4>
                <p class="mt-2 text-sm text-zinc-500 leading-relaxed">
                    Set up your secure client account in under a minute. Add funds using your preferred secure currency.
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200/80 p-8 shadow-xs hover:border-rose-200 transition-colors">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 font-bold text-lg">
                    2
                </div>
                <h4 class="mt-6 text-lg font-bold text-zinc-900">Configure Service</h4>
                <p class="mt-2 text-sm text-zinc-500 leading-relaxed">
                    Select target social platforms, specify destination link, and set desired order volume with upfront rate calculations.
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200/80 p-8 shadow-xs hover:border-rose-200 transition-colors">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 font-bold text-lg">
                    3
                </div>
                <h4 class="mt-6 text-lg font-bold text-zinc-900">Track Real-Time</h4>
                <p class="mt-2 text-sm text-zinc-500 leading-relaxed">
                    Observe status progressions from pending to processing and completed with live remains counters.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Service Catalog Overview Section -->
<section class="py-20 bg-zinc-50 border-t border-zinc-200/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-12">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600">Available Services</h2>
                <h3 class="mt-2 text-3xl font-bold tracking-tight text-zinc-900">Popular Growth Channels</h3>
            </div>
            <a href="/services" class="mt-4 md:mt-0 text-sm font-semibold text-rose-600 hover:text-rose-700">
                View All Services &rarr;
            </a>
        </div>

        <?php if (!empty($categories)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categories as $cat): ?>
                    <div class="rounded-2xl bg-white p-6 border border-zinc-200 shadow-xs hover:shadow-md transition-shadow">
                        <div class="flex items-center space-x-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-700 font-semibold">
                                &bull;
                            </span>
                            <h4 class="font-bold text-zinc-900"><?= e($cat['name']) ?></h4>
                        </div>
                        <p class="mt-3 text-xs text-zinc-500">Verified automated service packages with high retention and fast starts.</p>
                        <a href="/services" class="mt-4 inline-block text-xs font-medium text-rose-600 hover:underline">
                            Explore packages &rarr;
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Clean Empty State -->
            <div class="rounded-2xl bg-white p-12 text-center border border-zinc-200 shadow-xs max-w-md mx-auto">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600 mb-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h4 class="text-base font-bold text-zinc-900">Services Catalog Initializing</h4>
                <p class="mt-2 text-sm text-zinc-500">
                    Administrator is currently configuring active provider routes and category listings.
                </p>
                <a href="/register" class="mt-6 inline-block rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700">
                    Create Account for Updates
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Frequently Asked Questions -->
<section class="py-20 bg-white border-t border-zinc-200/60">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-rose-600">Clear Answers</h2>
            <h3 class="mt-2 text-3xl font-bold tracking-tight text-zinc-900">Frequently Asked Questions</h3>
        </div>

        <div class="mt-12 space-y-6">
            <div class="rounded-2xl border border-zinc-200 p-6">
                <h4 class="font-bold text-zinc-900">What is an SMM Panel?</h4>
                <p class="mt-2 text-sm text-zinc-600 leading-relaxed">
                    An SMM (Social Media Marketing) panel is an online platform that enables creators, agencies, and businesses to order social marketing engagements through automated API gateways.
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 p-6">
                <h4 class="font-bold text-zinc-900">How fast are orders processed?</h4>
                <p class="mt-2 text-sm text-zinc-600 leading-relaxed">
                    Orders start automatically through connected provider APIs immediately upon order verification. Detailed start times and speed metrics are listed on each specific service package.
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 p-6">
                <h4 class="font-bold text-zinc-900">Are funds safe and refundable?</h4>
                <p class="mt-2 text-sm text-zinc-600 leading-relaxed">
                    Yes. All client balances are tracked in real-time. If an automated service experiences delivery disruption, remaining balances are credited back to your account wallet.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Final Call to Action -->
<section class="py-16 bg-gradient-to-r from-rose-600 to-red-700 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Ready to Accelerate Your Social Channels?</h2>
        <p class="mt-4 text-base text-rose-100 max-w-xl mx-auto">
            Create an account today to access full service catalogs and high-speed delivery networks.
        </p>
        <div class="mt-8">
            <a href="/register" class="rounded-xl bg-white px-8 py-3.5 text-sm font-bold text-rose-700 shadow-lg hover:bg-rose-50 transition-colors">
                Get Started Now &rarr;
            </a>
        </div>
    </div>
</section>
