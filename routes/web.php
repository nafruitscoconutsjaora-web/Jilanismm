<?php

use App\Core\Router;
use App\Core\Response;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\OrderController;
use App\Controllers\PaymentController;
use App\Controllers\AdminAuthController;
use App\Controllers\AdminController;
use App\Controllers\AdminCategoryController;
use App\Controllers\AdminServiceController;
use App\Controllers\AdminProviderController;
use App\Controllers\AdminOrderController;
use App\Controllers\AdminPaymentController;
use App\Controllers\AdminWalletController;
use App\Controllers\AdminCurrencyController;
use App\Controllers\AdminCouponController;
use App\Controllers\AdminReferralController;
use App\Controllers\AdminTicketController;
use App\Controllers\AdminReportController;
use App\Controllers\ReferralController;
use App\Controllers\TicketController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\AdminGuestMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\MaintenanceMiddleware;

/** @var Router $router */

// 1. PUBLIC ROUTES (Checked for Maintenance Mode)
$router->get('/', [HomeController::class, 'index'], [MaintenanceMiddleware::class]);
$router->get('/services', [HomeController::class, 'services'], [MaintenanceMiddleware::class]);
$router->get('/terms', [HomeController::class, 'terms'], [MaintenanceMiddleware::class]);
$router->get('/privacy', [HomeController::class, 'privacy'], [MaintenanceMiddleware::class]);
$router->get('/contact', [HomeController::class, 'contact'], [MaintenanceMiddleware::class]);
$router->get('/verify-email', [AuthController::class, 'verifyEmail'], [MaintenanceMiddleware::class]);

// 2. USER GUEST AUTH ROUTES
$router->get('/login', [AuthController::class, 'showLogin'], [MaintenanceMiddleware::class, GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [MaintenanceMiddleware::class, GuestMiddleware::class, CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister'], [MaintenanceMiddleware::class, GuestMiddleware::class]);
$router->post('/register', [AuthController::class, 'register'], [MaintenanceMiddleware::class, GuestMiddleware::class, CsrfMiddleware::class]);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword'], [MaintenanceMiddleware::class, GuestMiddleware::class]);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink'], [MaintenanceMiddleware::class, GuestMiddleware::class, CsrfMiddleware::class]);
$router->get('/reset-password', [AuthController::class, 'showResetPassword'], [MaintenanceMiddleware::class, GuestMiddleware::class]);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [MaintenanceMiddleware::class, GuestMiddleware::class, CsrfMiddleware::class]);

// 3. USER AUTHENTICATED PANEL ROUTES
$router->get('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/dashboard', [UserController::class, 'dashboard'], [AuthMiddleware::class]);
$router->get('/user/services', [UserController::class, 'services'], [AuthMiddleware::class]);

// Orders
$router->get('/orders/new', [OrderController::class, 'newOrder'], [AuthMiddleware::class]);
$router->post('/orders/new', [OrderController::class, 'placeOrder'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/orders', [OrderController::class, 'index'], [AuthMiddleware::class]);
$router->get('/orders/{id}', [OrderController::class, 'show'], [AuthMiddleware::class]);
$router->get('/api/service/{id}', [OrderController::class, 'apiServiceDetails'], [AuthMiddleware::class]);
$router->get('/api/coupon/validate', [OrderController::class, 'validateCoupon'], [AuthMiddleware::class]);

// Wallet & Payments
$router->get('/wallet', [UserController::class, 'wallet'], [AuthMiddleware::class]);
$router->get('/transactions', [UserController::class, 'wallet'], [AuthMiddleware::class]);
$router->get('/wallet/add-funds', [PaymentController::class, 'addFunds'], [AuthMiddleware::class]);
$router->post('/wallet/add-funds', [PaymentController::class, 'createPayment'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/payment/checkout/{txn}', [PaymentController::class, 'checkout'], [AuthMiddleware::class]);
$router->get('/payment/callback/{gateway}', [PaymentController::class, 'callback'], [AuthMiddleware::class]);
$router->post('/payment/webhook/{gateway}', [PaymentController::class, 'webhook']);

$router->get('/notifications', [UserController::class, 'notifications'], [AuthMiddleware::class]);
$router->post('/notifications/read/{id}', [UserController::class, 'markNotificationRead'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/notifications/read-all', [UserController::class, 'markAllNotificationsRead'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/profile', [UserController::class, 'profile'], [AuthMiddleware::class]);
$router->post('/profile', [UserController::class, 'updateProfile'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/profile/password', [UserController::class, 'updatePassword'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/settings', [UserController::class, 'settings'], [AuthMiddleware::class]);
$router->get('/referrals', [ReferralController::class, 'index'], [AuthMiddleware::class]);

// Support Tickets
$router->get('/tickets', [TicketController::class, 'index'], [AuthMiddleware::class]);
$router->get('/tickets/create', [TicketController::class, 'showCreate'], [AuthMiddleware::class]);
$router->post('/tickets/create', [TicketController::class, 'create'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/tickets/{id}', [TicketController::class, 'show'], [AuthMiddleware::class]);
$router->post('/tickets/{id}/reply', [TicketController::class, 'reply'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/close', [TicketController::class, 'close'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/reopen', [TicketController::class, 'reopen'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/support', function() {
    Response::redirect('/tickets');
}, [AuthMiddleware::class]);

// 4. ADMIN GUEST AUTH ROUTES
$router->get('/admin/login', [AdminAuthController::class, 'showLogin'], [AdminGuestMiddleware::class]);
$router->post('/admin/login', [AdminAuthController::class, 'login'], [AdminGuestMiddleware::class, CsrfMiddleware::class]);

// 5. ADMIN AUTHENTICATED ROUTES
$router->get('/admin', function() {
    Response::redirect('/admin/dashboard');
}, [AdminMiddleware::class]);
$router->get('/admin/logout', [AdminAuthController::class, 'logout'], [AdminMiddleware::class]);
$router->post('/admin/logout', [AdminAuthController::class, 'logout'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [AdminMiddleware::class]);
$router->get('/admin/users', [AdminController::class, 'users'], [AdminMiddleware::class]);
$router->get('/admin/users/{id}', [AdminController::class, 'showUser'], [AdminMiddleware::class]);
$router->post('/admin/users/status', [AdminController::class, 'updateUserStatus'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/settings', [AdminController::class, 'settings'], [AdminMiddleware::class]);
$router->post('/admin/settings', [AdminController::class, 'updateSettings'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/maintenance', [AdminController::class, 'toggleMaintenance'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/notifications', [AdminController::class, 'notifications'], [AdminMiddleware::class]);
$router->post('/admin/notifications/send', [AdminController::class, 'sendNotification'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Reports
$router->get('/admin/reports', [AdminReportController::class, 'index'], [AdminMiddleware::class]);

// Admin Coupons
$router->get('/admin/coupons', [AdminCouponController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/coupons/create', [AdminCouponController::class, 'create'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/coupons/edit/{id}', [AdminCouponController::class, 'update'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/coupons/delete/{id}', [AdminCouponController::class, 'delete'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/coupons/usage', [AdminCouponController::class, 'usage'], [AdminMiddleware::class]);

// Admin Referrals
$router->get('/admin/referrals', [AdminReferralController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/referrals/settings', [AdminReferralController::class, 'updateSettings'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Tickets
$router->get('/admin/tickets', [AdminTicketController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/tickets/{id}', [AdminTicketController::class, 'show'], [AdminMiddleware::class]);
$router->post('/admin/tickets/{id}/reply', [AdminTicketController::class, 'reply'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/tickets/{id}/status', [AdminTicketController::class, 'updateStatus'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/tickets/{id}/assign', [AdminTicketController::class, 'assign'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Categories
$router->get('/admin/categories', [AdminCategoryController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/categories/create', [AdminCategoryController::class, 'create'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/categories/edit/{id}', [AdminCategoryController::class, 'update'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/categories/delete/{id}', [AdminCategoryController::class, 'delete'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Services
$router->get('/admin/services', [AdminServiceController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/services/create', [AdminServiceController::class, 'create'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/services/edit/{id}', [AdminServiceController::class, 'update'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/services/status/{id}', [AdminServiceController::class, 'toggleStatus'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Providers
$router->get('/admin/providers', [AdminProviderController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/providers/create', [AdminProviderController::class, 'create'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/providers/edit/{id}', [AdminProviderController::class, 'update'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/providers/status/{id}', [AdminProviderController::class, 'toggleStatus'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/providers/balance/{id}', [AdminProviderController::class, 'checkBalance'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/providers/services/{id}', [AdminProviderController::class, 'viewServices'], [AdminMiddleware::class]);
$router->post('/admin/providers/sync/{id}', [AdminProviderController::class, 'syncServices'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/providers/map', [AdminProviderController::class, 'mapService'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Orders
$router->get('/admin/orders', [AdminOrderController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/orders/{id}', [AdminOrderController::class, 'show'], [AdminMiddleware::class]);
$router->post('/admin/orders/status/{id}', [AdminOrderController::class, 'updateStatus'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/orders/refund/{id}', [AdminOrderController::class, 'refund'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Payments & Gateways
$router->get('/admin/payments', [AdminPaymentController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/payments/approve/{id}', [AdminPaymentController::class, 'approveManual'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/payments/reject/{id}', [AdminPaymentController::class, 'rejectPayment'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/gateways', [AdminPaymentController::class, 'gateways'], [AdminMiddleware::class]);
$router->post('/admin/gateways/edit/{id}', [AdminPaymentController::class, 'updateGateway'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Wallets
$router->get('/admin/wallets', [AdminWalletController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/wallets/adjust', [AdminWalletController::class, 'adjustBalance'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Currencies
$router->get('/admin/currencies', [AdminCurrencyController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/currencies/create', [AdminCurrencyController::class, 'create'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/currencies/update/{id}', [AdminCurrencyController::class, 'update'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/currencies/set-default/{id}', [AdminCurrencyController::class, 'setDefault'], [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Module Placeholders (fallback for remaining future modules)
$router->get('/admin/{module}', [AdminController::class, 'placeholderModule'], [AdminMiddleware::class]);
