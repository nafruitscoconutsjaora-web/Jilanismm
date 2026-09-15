<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Database\Database;
use App\Services\SettingsService;

class HomeController
{
    public function index(Request $request): void
    {
        // Query real categories and services count
        $servicesCount = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM `services` WHERE `status` = 'active'")['cnt'] ?? 0);
        $categories = Database::query("SELECT * FROM `categories` WHERE `status` = 'active' ORDER BY `sort_order` ASC LIMIT 6");

        View::render('public/home', [
            'title' => SettingsService::get('site_name', 'SMM Panel') . ' - High Performance SMM Services',
            'servicesCount' => $servicesCount,
            'categories' => $categories,
            'siteName' => SettingsService::get('site_name', 'SMM Panel'),
            'siteDesc' => SettingsService::get('site_description', 'Premium Social Media Marketing Growth Platform'),
        ], 'main');
    }

    public function services(Request $request): void
    {
        // Fetch real active services from database grouped by category
        $sql = "SELECT s.*, c.name as category_name 
                FROM `services` s 
                LEFT JOIN `categories` c ON s.category_id = c.id 
                WHERE s.status = 'active' 
                ORDER BY c.sort_order ASC, s.sort_order ASC";
        $services = Database::query($sql);

        View::render('public/services', [
            'title' => 'Services & Pricing - ' . SettingsService::get('site_name', 'SMM Panel'),
            'services' => $services,
        ], 'main');
    }

    public function terms(Request $request): void
    {
        View::render('public/terms', [
            'title' => 'Terms of Service - ' . SettingsService::get('site_name', 'SMM Panel'),
        ], 'main');
    }

    public function privacy(Request $request): void
    {
        View::render('public/privacy', [
            'title' => 'Privacy Policy - ' . SettingsService::get('site_name', 'SMM Panel'),
        ], 'main');
    }

    public function contact(Request $request): void
    {
        View::render('public/contact', [
            'title' => 'Contact Us - ' . SettingsService::get('site_name', 'SMM Panel'),
            'supportEmail' => SettingsService::get('support_email', 'support@smmpanel.local'),
        ], 'main');
    }
}
