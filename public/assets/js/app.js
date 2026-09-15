// Vanilla JavaScript for SMM Panel (Classic Theme)
document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.flash-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // 2. Mobile Sidebar Toggle
    const sidebarToggleBtn = document.getElementById('sidebar-toggle');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const mobileBackdrop = document.getElementById('mobile-sidebar-backdrop');
    const closeSidebarBtn = document.getElementById('close-sidebar');

    if (sidebarToggleBtn && mobileSidebar) {
        sidebarToggleBtn.addEventListener('click', () => {
            mobileSidebar.classList.remove('-translate-x-full');
            if (mobileBackdrop) mobileBackdrop.classList.remove('hidden');
        });
    }

    if (closeSidebarBtn && mobileSidebar) {
        closeSidebarBtn.addEventListener('click', () => {
            mobileSidebar.classList.add('-translate-x-full');
            if (mobileBackdrop) mobileBackdrop.classList.add('hidden');
        });
    }

    if (mobileBackdrop && mobileSidebar) {
        mobileBackdrop.addEventListener('click', () => {
            mobileSidebar.classList.add('-translate-x-full');
            mobileBackdrop.classList.add('hidden');
        });
    }

    // 3. User Dropdown Menu
    const userMenuBtn = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-dropdown');
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    }

    // 4. Copy Referral Code / Link
    const copyBtns = document.querySelectorAll('.copy-btn');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                const text = targetEl.value || targetEl.innerText;
                navigator.clipboard.writeText(text).then(() => {
                    const originalText = btn.innerText;
                    btn.innerText = 'Copied!';
                    btn.classList.add('bg-emerald-600', 'text-white');
                    setTimeout(() => {
                        btn.innerText = originalText;
                        btn.classList.remove('bg-emerald-600', 'text-white');
                    }, 2000);
                });
            }
        });
    });

    // 5. Password visibility toggler
    const togglePassBtns = document.querySelectorAll('.toggle-password');
    togglePassBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const inputId = btn.getAttribute('data-input');
            const input = document.getElementById(inputId);
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.innerText = 'Hide';
                } else {
                    input.type = 'password';
                    btn.innerText = 'Show';
                }
            }
        });
    });
});
