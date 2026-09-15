<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\Category;
use App\Models\Provider;
use App\Models\ProviderService;
use App\Services\AuthService;
use App\Services\ProviderServiceManager;
use App\Services\Provider\ProviderFactory;

class AdminProviderController
{
    public function index(Request $request): void
    {
        $admin = AuthService::admin();
        $providers = Provider::allWithServiceCount();

        View::render('admin/providers/index', [
            'title' => 'API Providers - Admin SMM Panel',
            'admin' => $admin,
            'providers' => $providers
        ], 'admin');
    }

    public function create(Request $request): void
    {
        $name = trim($request->input('name', ''));
        $apiUrl = trim($request->input('api_url', ''));
        $apiKey = trim($request->input('api_key', ''));
        $currency = strtoupper(trim($request->input('currency', 'USD')));
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';
        $description = trim($request->input('description', ''));

        if (empty($name) || empty($apiUrl) || empty($apiKey)) {
            Session::setFlash('error', 'Provider name, API URL, and API key are required.');
            Response::redirect('/admin/providers');
            return;
        }

        Database::execute(
            "INSERT INTO `providers` (`name`, `api_url`, `api_key`, `currency`, `status`, `description`, `created_at`) 
             VALUES (:name, :url, :key, :curr, :st, :desc, NOW())",
            [
                ':name' => $name,
                ':url' => $apiUrl,
                ':key' => $apiKey,
                ':curr' => $currency,
                ':st' => $status,
                ':desc' => $description
            ]
        );

        Session::setFlash('success', "Provider '{$name}' created successfully.");
        Response::redirect('/admin/providers');
    }

    public function edit(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $provider = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$provider) {
            Session::setFlash('error', 'Provider not found.');
            Response::redirect('/admin/providers');
            return;
        }

        $name = trim($request->input('name', ''));
        $apiUrl = trim($request->input('api_url', ''));
        $apiKey = trim($request->input('api_key', ''));
        $currency = strtoupper(trim($request->input('currency', 'USD')));
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';
        $description = trim($request->input('description', ''));

        // If api_key left empty, keep existing
        $keyToUse = !empty($apiKey) ? $apiKey : $provider['api_key'];

        Database::execute(
            "UPDATE `providers` SET `name` = :name, `api_url` = :url, `api_key` = :key, `currency` = :curr, `status` = :st, `description` = :desc, `updated_at` = NOW() 
             WHERE `id` = :id",
            [
                ':name' => $name,
                ':url' => $apiUrl,
                ':key' => $keyToUse,
                ':curr' => $currency,
                ':st' => $status,
                ':desc' => $description,
                ':id' => $id
            ]
        );

        Session::setFlash('success', 'Provider updated successfully.');
        Response::redirect('/admin/providers');
    }

    public function checkBalance(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $provider = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$provider) {
            Session::setFlash('error', 'Provider not found.');
            Response::redirect('/admin/providers');
            return;
        }

        try {
            $adapter = ProviderFactory::make($provider);
            $res = $adapter->getBalance();

            if ($res['success']) {
                $bal = (float)$res['balance'];
                $curr = $res['currency'] ?? $provider['currency'];
                Database::execute(
                    "UPDATE `providers` SET `balance` = :bal, `updated_at` = NOW() WHERE `id` = :id",
                    [':bal' => $bal, ':id' => $id]
                );

                Session::setFlash('success', "Live balance for {$provider['name']}: " . number_format($bal, 2) . " {$curr}");
            } else {
                Session::setFlash('error', "Failed to fetch balance from {$provider['name']}: " . ($res['error'] ?? 'API error'));
            }
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Provider connection error: ' . $e->getMessage());
        }

        Response::redirect('/admin/providers');
    }

    public function viewServices(Request $request, array $params): void
    {
        $admin = AuthService::admin();
        $id = (int)($params['id'] ?? 0);
        $provider = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$provider) {
            Session::setFlash('error', 'Provider not found.');
            Response::redirect('/admin/providers');
            return;
        }

        $providerServices = ProviderService::forProvider($id);
        $categories = Category::all();

        View::render('admin/providers/services', [
            'title' => "{$provider['name']} - Services - Admin SMM Panel",
            'admin' => $admin,
            'provider' => $provider,
            'providerServices' => $providerServices,
            'categories' => $categories
        ], 'admin');
    }

    public function syncServices(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $provider = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$provider) {
            Session::setFlash('error', 'Provider not found.');
            Response::redirect('/admin/providers');
            return;
        }

        $result = ProviderServiceManager::syncServicesFromProvider($id);

        if ($result['success']) {
            Session::setFlash('success', "Successfully synced services from {$provider['name']}! Total: {$result['total']} ({$result['imported']} new imported, {$result['updated']} updated).");
        } else {
            Session::setFlash('error', "Service sync failed: " . ($result['error'] ?? 'API error'));
        }

        Response::redirect('/admin/providers/services/' . $id);
    }

    public function mapService(Request $request): void
    {
        $res = ProviderServiceManager::mapToPanelService($request->all());

        if ($res['success']) {
            Session::setFlash('success', 'Service successfully mapped and added to customer catalog!');
        } else {
            Session::setFlash('error', 'Mapping failed: ' . ($res['error'] ?? 'Unknown error'));
        }

        $providerId = (int)$request->input('provider_id');
        Response::redirect($providerId > 0 ? '/admin/providers/services/' . $providerId : '/admin/services');
    }

    public function toggleStatus(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $provider = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$provider) {
            Session::setFlash('error', 'Provider not found.');
            Response::redirect('/admin/providers');
            return;
        }

        $newStatus = $provider['status'] === 'active' ? 'inactive' : 'active';
        Database::execute("UPDATE `providers` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id", [':st' => $newStatus, ':id' => $id]);

        Session::setFlash('success', "Provider '{$provider['name']}' status set to {$newStatus}.");
        Response::redirect('/admin/providers');
    }
}
