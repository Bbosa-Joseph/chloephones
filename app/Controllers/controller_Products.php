<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Models\Model_notifications;
use App\Models\Model_products;
use App\Models\Model_stores;

class Controller_Products extends Admin_Controller
{
	public function __construct()
	{
		$this->not_logged_in();
		$this->data['page_title'] = 'Products';
	}

	public function index()
	{
		if (!in_array('viewProduct', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$storesModel = new Model_stores();
		$this->data['warehouses'] = $this->isAdminUser()
			? $storesModel->getStoresData()
			: $storesModel->getAssignedStores($this->currentUserId(), true);
		return $this->render_template('products/index', $this->data);
	}

	public function fetchProductData()
	{
		$result = ['data' => []];
		$productsModel = new Model_products();
		$storesModel = new Model_stores();
		$warehouseMap = [];
		foreach ($this->getAccessibleWarehouses($storesModel) as $store) {
			$warehouseMap[(int) $store['id']] = $store['name'] ?? '';
		}

		$data = $this->getAccessibleProducts($productsModel, $storesModel);
		foreach ($data as $key => $value) {
			$buttons = '';
			if (in_array('updateProduct', $this->permission)) {
				$buttons .= '<a href="' . base_url('Controller_Products/update/' . $value['id']) . '" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>';
			}
			if (in_array('printProduct', $this->permission)) {
				$buttons .= ' <a href="' . base_url('Controller_Products/printProduct/' . $value['id']) . '" class="btn btn-primary btn-sm" title="Print"><i class="fa fa-print"></i></a>';
			}
			if (in_array('createOrder', $this->permission)) {
				$buttons .= ' <a href="' . base_url('Controller_Orders/create?product_id=' . $value['id'] . '&imei=' . urlencode($value['imei'])) . '" class="btn btn-info btn-sm" title="Create Order"><i class="fa fa-shopping-cart"></i></a>';
			}
			if (in_array('deleteProduct', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-danger btn-sm" onclick="removeFunc(' . $value['id'] . ')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
			}

			$availability = ((int) $value['availability'] === 1) ? 'In Stock' : 'Out of Stock';
			$warehouseName = 'Unassigned';
			if (!empty($value['warehouse_id'])) {
				$warehouseName = $warehouseMap[(int) $value['warehouse_id']] ?? 'Unassigned';
			}

			$result['data'][$key] = [
				'id' => $value['id'],
				'name' => $value['name'],
				'imei' => $value['imei'],
				'price' => 'UGX' . $value['price'],
				'warehouse' => $warehouseName,
				'availability' => $availability,
				'date_added' => $value['date_added'] ?? '',
				'stock_status' => '',
				'actions' => $buttons,
			];
		}

		return $this->response->setJSON($result);
	}

	public function printProduct($productId)
	{
		if (!in_array('printProduct', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		if (!$productId) {
			return redirect()->to(base_url('Controller_Products'));
		}

		$productsModel = new Model_products();
		$product = $productsModel->getProductData($productId);
		if (!$product) {
			return redirect()->to(base_url('Controller_Products'));
		}

		$imei = urlencode((string) ($product['imei'] ?? ''));
		return redirect()->to(base_url('Controller_Orders/create?product_id=' . $productId . '&imei=' . $imei));
	}

	public function productReceipt($productId)
	{
		if (!in_array('printProduct', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		if (!$productId) {
			return redirect()->to(base_url('Controller_Products'));
		}

		$productsModel = new Model_products();
		$storesModel = new Model_stores();
		$product = $productsModel->getProductData($productId);
		if (!$product) {
			return redirect()->to(base_url('Controller_Products'));
		}

		$warehouseName = 'Unassigned';
		if (!empty($product['warehouse_id'])) {
			$store = $storesModel->getStoresData($product['warehouse_id']);
			$warehouseName = $store ? $store['name'] : 'Unassigned';
		}

		$receiptDate = Time::now(app_timezone())->format('Y-m-d H:i');

		$html = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Product Receipt</title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<style>
body { font-family: "Segoe UI", Arial, sans-serif; color: #1f2937; background: #f8fafc; }
.ticket { max-width: 420px; margin: 30px auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; box-shadow: 0 6px 18px rgba(15,23,42,0.08); }
.ticket h1 { font-size: 18px; margin: 0 0 6px 0; color: #0f4c81; }
.ticket small { color: #64748b; }
.row { display: flex; justify-content: space-between; margin: 8px 0; font-size: 14px; }
.label { color: #64748b; }
.value { font-weight: 600; }
.actions { text-align: center; margin-top: 16px; }
.btn { background: #0f4c81; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
@media print { .actions { display: none; } body { background: #fff; } .ticket { box-shadow: none; border: none; } }
</style>
</head>
<body>
<div class="ticket">
<h1>Product Receipt</h1>
<small>' . $receiptDate . '</small>
<div class="row"><span class="label">Product</span><span class="value">' . htmlspecialchars($product['name'] ?? '') . '</span></div>
<div class="row"><span class="label">IMEI</span><span class="value">' . htmlspecialchars($product['imei'] ?? '') . '</span></div>
<div class="row"><span class="label">Price</span><span class="value">UGX ' . number_format((float) ($product['price'] ?? 0), 0) . '</span></div>
<div class="row"><span class="label">Warehouse</span><span class="value">' . htmlspecialchars($warehouseName) . '</span></div>
<div class="row"><span class="label">Status</span><span class="value">' . (((int) ($product['availability'] ?? 0) === 1) ? 'Available' : 'Not Available') . '</span></div>
<div class="actions"><button class="btn" onclick="window.print()">Print</button></div>
</div>
</body>
</html>';

		return $this->response->setBody($html);
	}

	public function create()
    {
        if (!in_array('createProduct', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $rules = [
            'product_name' => 'required',
            'price'        => 'required|numeric',
            'availability' => 'required',
        ];

        $productsModel = new Model_products();
        $storesModel   = new Model_stores();
		$availableWarehouses = $this->getAccessibleWarehouses($storesModel, true);

        if ($this->validate($rules)) {

            // Get both inputs
            $imeiList = $this->request->getPost('imei_list'); // array
            $imeiBulk = $this->request->getPost('imei_bulk'); // textarea

            $imeis = [];

            // 1. From dynamic fields
            if (!empty($imeiList)) {
                foreach ($imeiList as $imei) {
                    $imei = trim($imei);
                    if (!empty($imei)) {
                        $imeis[] = $imei;
                    }
                }
            }

            // 2. From bulk paste
            if (!empty($imeiBulk)) {
                $bulkArray = preg_split('/[\s,]+/', trim($imeiBulk));
                foreach ($bulkArray as $imei) {
                    $imei = trim($imei);
                    if (!empty($imei)) {
                        $imeis[] = $imei;
                    }
                }
            }

            // Remove duplicates from input itself
            $imeis = array_unique($imeis);

            if (empty($imeis)) {
                session()->setFlashdata('errors', 'Please enter at least one IMEI.');
                return redirect()->back();
            }

            $duplicateImeis = [];
            $inserted = 0;

            $commonData = [
                'name'         => $this->request->getPost('product_name'),
                'price'        => $this->request->getPost('price'),
                'description'  => $this->request->getPost('description'),
                'storage'      => $this->request->getPost('storage'),
                'ram'          => $this->request->getPost('ram'),
                'warehouse_id' => $this->request->getPost('warehouse_id'),
                'availability' => $this->request->getPost('availability'),
                'date_added'   => date('Y-m-d'),
            ];

			if (!$this->canAccessWarehouse((int) $commonData['warehouse_id'], $storesModel)) {
				session()->setFlashdata('errors', 'You can only assign products to your own warehouse.');
				return redirect()->back()->withInput();
			}

            foreach ($imeis as $imei) {

                // Check DB duplicate
                $existing = $productsModel->getProductByIMEI($imei);
                if ($existing) {
                    $duplicateImeis[] = $imei;
                    continue;
                }

                $data = $commonData;
                $data['imei'] = $imei;

                $productsModel->create($data);
                $inserted++;
            }

            // Feedback
            if (!empty($duplicateImeis)) {
                session()->setFlashdata(
                    'errors',
                    'Duplicate IMEIs skipped: ' . implode(', ', $duplicateImeis)
                );
            }

            session()->setFlashdata(
                'success',
                "$inserted phone(s) added successfully"
            );

            return redirect()->to(base_url('Controller_Products'));
        }

		$this->data['warehouses'] = $availableWarehouses;
        return $this->render_template('products/create', $this->data);
    }

	public function update($productId)
	{
		if (!in_array('updateProduct', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		if (!$productId) {
			return redirect()->to(base_url('dashboard'));
		}

		$rules = [
			'product_name' => 'required',
			'price' => 'required|numeric',
			'availability' => 'required',
		];

		$productsModel = new Model_products();
		$storesModel = new Model_stores();
		$product = $this->getAccessibleProduct($productId, $productsModel, $storesModel);
		if (!$product) {
			session()->setFlashdata('error', 'You do not have access to that product.');
			return redirect()->to(base_url('Controller_Products'));
		}

		if ($this->validate($rules)) {
			$data = [
				'name' => $this->request->getPost('product_name'),
				'imei' => $this->request->getPost('imei'),
				'price' => $this->request->getPost('price'),
				'description' => $this->request->getPost('description'),
				'storage' => $this->request->getPost('storage'),
				'ram' => $this->request->getPost('ram'),
				'warehouse_id' => $this->request->getPost('warehouse_id'),
				'availability' => $this->request->getPost('availability'),
			];

			if (!$this->canAccessWarehouse((int) $data['warehouse_id'], $storesModel)) {
				session()->setFlashdata('errors', 'You can only move products within your own warehouse assignments.');
				return redirect()->to(base_url('Controller_Products/update/' . $productId));
			}

			$imei = $this->request->getPost('imei');
			$existing = $productsModel->getProductByIMEI($imei);
			if ($existing && (int) $existing['id'] !== (int) $productId) {
				session()->setFlashdata('errors', 'A product with this IMEI already exists. Please use a unique IMEI.');
				return redirect()->to(base_url('Controller_Products/update/' . $productId));
			}

			$update = $productsModel->update($data, $productId);
			if ($update) {
				session()->setFlashdata('success', 'Successfully updated');
				return redirect()->to(base_url('Controller_Products'));
			}

			session()->setFlashdata('errors', 'Error occurred!!');
			return redirect()->to(base_url('Controller_Products/update/' . $productId));
		}

		$this->data['product_data'] = $product;
		$this->data['warehouses'] = $this->getAccessibleWarehouses($storesModel, true);
		return $this->render_template('products/edit', $this->data);
	}

	public function remove()
	{
		if (!in_array('deleteProduct', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$productId = $this->request->getPost('product_id');
		$response = [];
		if ($productId) {
			$productsModel = new Model_products();
			$storesModel = new Model_stores();
			$notificationsModel = new Model_notifications();

			$product = $this->getAccessibleProduct($productId, $productsModel, $storesModel);
			if (!$product) {
				$response['success'] = false;
				$response['messages'] = 'You do not have access to that product';
				if ($this->request->isAJAX()) {
					return $this->response->setJSON($response);
				}

				session()->setFlashdata('error', $response['messages']);
				return redirect()->to(base_url('Controller_Products'));
			}

			$delete = $productsModel->remove($productId);
			if ($delete) {
				if (!empty($product['warehouse_id'])) {
					$adminName = session()->get('username');
					$msg = 'Product "' . $product['name'] . '" (IMEI: ' . $product['imei'] . ') was removed by ' . $adminName;
					$notificationsModel->notifyWarehouseMembers($product['warehouse_id'], $msg, 'warning');
				}
				$response['success'] = true;
				$response['messages'] = 'Successfully removed';
			} else {
				$response['success'] = false;
				$response['messages'] = 'Error in the database while removing the product information';
			}
		} else {
			$response['success'] = false;
			$response['messages'] = 'Refresh the page again!!';
		}

		if ($this->request->isAJAX()) {
			return $this->response->setJSON($response);
		}

		if (!empty($response['success'])) {
			session()->setFlashdata('success', $response['messages']);
		} else {
			session()->setFlashdata('error', $response['messages']);
		}

		return redirect()->to(base_url('Controller_Products'));
	}

	public function bulkRemove()
	{
		if (!in_array('deleteProduct', $this->permission)) {
			if ($this->request->isAJAX()) {
				return $this->response->setJSON(['success' => false, 'messages' => 'Permission denied']);
			}
			return redirect()->to(base_url('dashboard'));
		}

		$ids = $this->request->getPost('ids');
		$response = [];

		if (!empty($ids) && is_array($ids)) {
			$productsModel = new Model_products();
			$storesModel = new Model_stores();
			$notificationsModel = new Model_notifications();
			$successCount = 0;
			$adminName = session()->get('username');
			$notifiedWarehouses = [];

			foreach ($ids as $id) {
				$id = (int) $id;
				if ($id > 0) {
					$product = $this->getAccessibleProduct($id, $productsModel, $storesModel);
					if (!$product) {
						continue;
					}

					if ($productsModel->remove($id)) {
						$successCount++;
						if (!empty($product['warehouse_id'])) {
							$wid = $product['warehouse_id'];
							if (!isset($notifiedWarehouses[$wid])) {
								$notifiedWarehouses[$wid] = [];
							}
							$notifiedWarehouses[$wid][] = $product['name'];
						}
					}
				}
			}

			foreach ($notifiedWarehouses as $wid => $names) {
				$count = count($names);
				if ($count === 1) {
					$msg = 'Product "' . $names[0] . '" was removed by ' . $adminName;
				} else {
					$msg = $count . ' products (' . implode(', ', array_slice($names, 0, 3));
					if ($count > 3) {
						$msg .= '...';
					}
					$msg .= ') were removed by ' . $adminName;
				}
				$notificationsModel->notifyWarehouseMembers($wid, $msg, 'warning');
			}

			$response['success'] = true;
			$response['messages'] = $successCount . ' product(s) successfully removed';
		} else {
			$response['success'] = false;
			$response['messages'] = 'No products selected';
		}

		if ($this->request->isAJAX()) {
			return $this->response->setJSON($response);
		}

		if (!empty($response['success'])) {
			session()->setFlashdata('success', $response['messages']);
		} else {
			session()->setFlashdata('error', $response['messages']);
		}

		return redirect()->to(base_url('Controller_Products'));
	}

	private function getAccessibleWarehouses(Model_stores $storesModel, bool $activeOnly = false): array
	{
		if ($this->isAdminUser()) {
			return $activeOnly ? $storesModel->getActiveStore() : $storesModel->getStoresData();
		}

		return $storesModel->getAssignedStores($this->currentUserId(), $activeOnly);
	}

	private function getAccessibleProducts(Model_products $productsModel, Model_stores $storesModel): array
	{
		if ($this->isAdminUser()) {
			return $productsModel->getProductData();
		}

		$warehouseIds = $storesModel->getAssignedStoreIds($this->currentUserId());
		return $productsModel->getProductsByWarehouseIds($warehouseIds);
	}

	private function getAccessibleProduct($productId, Model_products $productsModel, Model_stores $storesModel)
	{
		if ($this->isAdminUser()) {
			return $productsModel->getProductData($productId);
		}

		$warehouseIds = $storesModel->getAssignedStoreIds($this->currentUserId());
		return $productsModel->getProductByIdAndWarehouseIds($productId, $warehouseIds);
	}

	private function canAccessWarehouse(int $warehouseId, Model_stores $storesModel): bool
	{
		if ($warehouseId <= 0) {
			return false;
		}

		if ($this->isAdminUser()) {
			return $storesModel->getStoresData($warehouseId) !== null;
		}

		return $storesModel->isUserAssignedToStore($warehouseId, $this->currentUserId());
	}
}
