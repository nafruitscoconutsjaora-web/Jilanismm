<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Database\Database;
use App\Models\Category;
use App\Services\AuthService;
use App\Validation\Validator;

class AdminCategoryController
{
    public function index(Request $request): void
    {
        $admin = AuthService::admin();
        $categories = Category::allWithCount();

        View::render('admin/categories/index', [
            'title' => 'Categories - Admin SMM Panel',
            'admin' => $admin,
            'categories' => $categories
        ], 'admin');
    }

    public function create(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
        ]);

        if (!$validator->validate()) {
            Session::setFlash('error', $validator->firstError());
            Response::redirect('/admin/categories');
            return;
        }

        $name = trim($request->input('name'));
        $icon = trim($request->input('icon', 'tag'));
        $sortOrder = (int)$request->input('sort_order', 0);
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';

        Database::execute(
            "INSERT INTO `categories` (`name`, `icon`, `sort_order`, `status`, `created_at`) VALUES (:name, :icon, :sort, :st, NOW())",
            [':name' => $name, ':icon' => $icon, ':sort' => $sortOrder, ':st' => $status]
        );

        Session::setFlash('success', "Category '{$name}' created successfully.");
        Response::redirect('/admin/categories');
    }

    public function edit(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $category = Database::fetch("SELECT * FROM `categories` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$category) {
            Session::setFlash('error', 'Category not found.');
            Response::redirect('/admin/categories');
            return;
        }

        $name = trim($request->input('name'));
        $icon = trim($request->input('icon', 'tag'));
        $sortOrder = (int)$request->input('sort_order', 0);
        $status = $request->input('status') === 'inactive' ? 'inactive' : 'active';

        if (empty($name)) {
            Session::setFlash('error', 'Category name cannot be empty.');
            Response::redirect('/admin/categories');
            return;
        }

        Database::execute(
            "UPDATE `categories` SET `name` = :name, `icon` = :icon, `sort_order` = :sort, `status` = :st, `updated_at` = NOW() WHERE `id` = :id",
            [':name' => $name, ':icon' => $icon, ':sort' => $sortOrder, ':st' => $status, ':id' => $id]
        );

        Session::setFlash('success', 'Category updated successfully.');
        Response::redirect('/admin/categories');
    }

    public function toggleStatus(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $category = Database::fetch("SELECT * FROM `categories` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$category) {
            Session::setFlash('error', 'Category not found.');
            Response::redirect('/admin/categories');
            return;
        }

        $newStatus = $category['status'] === 'active' ? 'inactive' : 'active';
        Database::execute("UPDATE `categories` SET `status` = :st, `updated_at` = NOW() WHERE `id` = :id", [':st' => $newStatus, ':id' => $id]);

        Session::setFlash('success', "Category '{$category['name']}' status changed to {$newStatus}.");
        Response::redirect('/admin/categories');
    }

    public function delete(Request $request, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $category = Database::fetch("SELECT * FROM `categories` WHERE `id` = :id LIMIT 1", [':id' => $id]);

        if (!$category) {
            Session::setFlash('error', 'Category not found.');
            Response::redirect('/admin/categories');
            return;
        }

        $activeServices = Database::fetch("SELECT COUNT(*) as cnt FROM `services` WHERE `category_id` = :id", [':id' => $id])['cnt'] ?? 0;
        if ($activeServices > 0) {
            Session::setFlash('error', "Cannot delete category '{$category['name']}' because it contains {$activeServices} linked services. Please reassign or delete the services first.");
            Response::redirect('/admin/categories');
            return;
        }

        Database::execute("DELETE FROM `categories` WHERE `id` = :id", [':id' => $id]);
        Session::setFlash('success', "Category '{$category['name']}' deleted.");
        Response::redirect('/admin/categories');
    }
}
