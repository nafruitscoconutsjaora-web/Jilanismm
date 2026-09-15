<div class="flex min-h-[60vh] flex-col items-center justify-center text-center px-4">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 mb-6">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
    </div>
    <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl">System Under Maintenance</h1>
    <p class="mt-3 text-base text-zinc-600 max-w-md mx-auto leading-relaxed">
        <?= e($siteName) ?> is currently undergoing scheduled performance enhancements and API node synchronization. We will be back online shortly.
    </p>

    <div class="mt-8">
        <a href="/admin/login" class="text-xs text-zinc-400 hover:text-zinc-600">
            Authorized Personnel / Admin Gateway &rarr;
        </a>
    </div>
</div>
