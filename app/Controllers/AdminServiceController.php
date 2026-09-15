<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use App\Services\AuthService;
use App\Services\CurrencyService;
use App\Services\PricingService;

class AdminServiceController
{
    public function index(Request $request): void
    {
        $admin = AuthService::admin();
        $services = Service::allAdminWithRelations();
        $categories = Category::all();
        $providers = Provider::all();

        View::render('admin/services/index', [
            'title' => 'Services - Admin SMM Panel',
            'admin' => $admin,
            'services' => $services,
            'categories' => $categories,
            'providers' => $providers
        ], 'admin');
    }

    public function create(Request $request): void
    {
        $categoryId = (int)$request->input('category_id');
        $name = trim($request->input('name', ''));
        $providerId = (int)$request->input('provider_id', 0) ?: null;
        $providerServiceId = trim($request->input('provider_service_id', '')) ?: null;
        $minQty = max(1, (int)$request->input('min_quantity', 10));
        $maxQty = max($minQty, (int)$request->input('max_quantity', 10000));
        $serviceType = trim($request->input('service_type', 'default'));
        $markupType = $request->input('markup_type') === 'fixed' ? 'fixed' : 'percentage';
        $markupValue = (float)$request->input('markup_value', 0);
        $customerPrice = (float)$request->input('customer_price', 0);
        $providerOriginalPrice = (float)$request->input('provider_original_price', 0);
        $description = trim($request->input('description', ''));
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';

        if (empty($name) || $categoryId <= 0) {
            Session::setFlash('error', 'Service name and category are required.');
            Response::redirect('/admin/services');
            return;
        }

        // If provider cost is given, calculate markup
        $providerCurrency = 'USD';
        if ($providerId) {
            $p = Database::fetch("SELECT * FROM `providers` WHERE `id` = :id LIMIT 1", [':id' => $providerId]);
            if ($p) $providerCurrency = $p['currency'] ?? 'USD';
        }

        $calc = PricingService::calculateCustomerPrice($providerOriginalPrice, $providerCurrency, 'USD', $markupType, $markupValue);
        $finalPrice = $customerPrice > 0 ? $customerPrice : (float)$calc['customer_price'];

        Database::execute(
            "INSERT INTO `services` (
                `category_id`, `name`, `service_type`, `provider_id`, `provider_service_id`,
                `provider_original_price`, `provider_currency`, `exchange_rate`, `converted_provider_cost`,
                `markup_type`, `markup_value`, `customer_price`, `price_per_k`, `panel_currency`,
                `min_quantity`, `max_quantity`, `description`, `status`, `created_at`
             ) VALUES (
                :cat, :name, :stype, :pid, :psid,
                :pop, :pcurr, :erate, :cpc,
                :mtype, :mval, :cprice, :ppk, 'USD',
                :minq, :maxq, :desc, :st, NOW()
             )",
            [
                ':cat' => $categoryId,
                ':name' => $name,
                ':stype' => $serviceType,
                ':pid' => $providerId,
                ':psid' => $providerServiceId,
                ':pop' => $calc['provider_original_price'],
                ':pcurr' => $calc['provider_currency'],
                ':erate' => $calc['exchange_rate'],
                ':cpc' => $calc['converted_provider_cost'],
                ':mtype' => $calc['markup_type'],
                ':mval' => $calc['markup_value'],
                ':cprice' => $finalPrice,
                ':ppk' => $finalPrice,
                ':minq' => $minQty,
                ':maxq' => $maxQty,
                ':desc' => $description,
                ':st' => $status
            ]
        );

        Session::setFlash('success', "Service '{$name}' created successfully.");
        Response::redirect('/admin/services');
    }

    public function edit(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $service = Database::fetch("SELECT * FROM `services` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$service) {
            Session::setFlash('error', 'Service not found.');
            Response::redirect('/admin/services');
            return;
        }

        $categoryId = (int)$request->input('category_id');
        $name = trim($request->input('name', ''));
        $customerPrice = (float)$request->input('customer_price', 0);
        $minQty = (int)$request->input('min_quantity', 10);
        $maxQty = (int)$request->input('max_quantity', 10000);
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';
        $description = trim($request->input('description', ''));

        Database::execute(
            "UPDATE `services` SET 
                `category_id` = :cat, 
                `name` = :name, 
                `customer_price` = :cp, 
                `price_per_k` = :ppk, 
                `min_quantity` = :minq, 
                `max_quantity` = :maxq, 
                `status` = :st, 
                `description` = :desc,
                `updated_at` = NOW() 
             WHERE `id` = :id",
            [
                ':cat' => $categoryId,
                ':name' => $name,
                ':cp' => $customerPrice,
                ':ppk' => $customerPrice,
                ':minq' => $minQty,
                ':maxq' => $maxQty,
                ':st' => $status,
                ':desc' => $description,
                ':id' => $id
            ]
        );

        Session::setFlash('success', 'Service updated successfully.');
        Response::redirect('/admin/services');
    }

    public function toggleStatus(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $service = Database::fetch("SELECT * FROM `services` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$service) {
            Session::setFlash('error', 'Service not found.');
            Response::redirect('/admin/services');
            return;
        }

        $newStatus = $service['status'] === 'active' ? 'inactive' : 'active';
        Database::execute("UPDATE `services` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id", [':st' => $newStatus, ':id' => $id]);

        Session::setFlash('success', "Service '{$service['name']}' is now {$newStatus}.");
        Response::redirect('/admin/services');
    }

    public function delete(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        Database::execute("DELETE FROM `services` WHERE `id` = :id", [':id' => $id]);

        Session::setFlash('success', 'Service deleted successfully.');
        Response::redirect('/admin/services');
    }
}
