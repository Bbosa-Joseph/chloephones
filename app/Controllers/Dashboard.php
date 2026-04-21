<?php

namespace App\Controllers;

use App\Models\Model_orders;
use App\Models\Model_products;
use App\Models\Model_stores;
use App\Models\Model_users;

class Dashboard extends Admin_Controller
{
    public function __construct()
    {
        $this->not_logged_in();
        $this->data['page_title'] = 'Dashboard';
    }

    public function index()
    {
        $productsModel = new Model_products();
        $ordersModel = new Model_orders();
        $usersModel = new Model_users();
        $storesModel = new Model_stores();

        $this->data['total_products'] = $productsModel->countTotalProducts();
        $this->data['out_of_stock'] = $productsModel->countOutOfStock();
        $this->data['aged_products'] = $productsModel->countAgedProducts();
        $this->data['total_stock_value'] = $productsModel->getTotalStockValue();

        $this->data['total_paid_orders'] = $ordersModel->countTotalPaidOrders();
        $this->data['total_unpaid_orders'] = $ordersModel->countTotalUnpaidOrders();

        $this->data['total_users'] = $usersModel->countTotalUsers();
        $this->data['total_stores'] = $storesModel->countTotalStores();

        $userId = (int) session()->get('user_id');
        $group = $usersModel->getUserGroup($userId);
        $groupId = (int) ($group['id'] ?? 0);
        $groupName = strtolower((string) ($group['group_name'] ?? ''));
        $this->data['is_admin'] = ($groupId === 1) || (strpos($groupName, 'super') !== false);

        return $this->render_template('dashboard', $this->data);
    }

    public function memberProducts()
    {
        $userId = (int) session()->get('user_id');
        $products = [];

        $rows = db_connect()->query(
            'SELECT p.id, p.name, p.imei, p.availability, s.name as warehouse_name
             FROM products p
             JOIN stores s ON s.id = p.warehouse_id
             WHERE s.assigned_user_id = ?',
            [$userId]
        )->getResultArray();

        foreach ($rows as $row) {
            $products[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => $row['name'] ?? 'N/A',
                'imei' => $row['imei'] ?? 'N/A',
                'warehouse' => $row['warehouse_name'] ?? '',
                'status' => ((int) ($row['availability'] ?? 0) === 1) ? 'Available' : 'Sold',
            ];
        }

        return $this->response->setJSON([
            'count' => count($products),
            'products' => $products,
        ]);
    }
}