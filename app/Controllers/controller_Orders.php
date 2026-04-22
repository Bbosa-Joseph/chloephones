<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Models\Model_company;
use App\Models\Model_orders;
use App\Models\Model_products;

class Controller_Orders extends Admin_Controller
{
	public function __construct()
	{
		$this->not_logged_in();
		$this->data['page_title'] = 'Orders';
	}

	public function index()
	{
		if (!in_array('viewOrder', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$this->data['page_title'] = 'Manage Orders';
		return $this->render_template('orders/index', $this->data);
	}

	public function fetchOrdersData()
	{
		$result = ['data' => []];
		$ordersModel = new Model_orders();

		$data = $ordersModel->getOrdersData();
		foreach ($data as $key => $value) {
			$checkbox = '<input type="checkbox" class="order-checkbox" value="' . $value['id'] . '">';
			$buttons = '';

			if (in_array('printOrder', $this->permission)) {
				$buttons .= '<button type="button" class="btn btn-primary btn-sm" title="Print" onclick="openReceiptModal(' . $value['id'] . ')"><i class="fa fa-print"></i></button>';
			}
			if (in_array('returnOrder', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-success btn-sm" title="Return to Stock" onclick="returnFunc(' . $value['id'] . ')" data-toggle="modal" data-target="#returnModal"><i class="fa fa-undo"></i></button>';
			}
			if (in_array('updateOrder', $this->permission)) {
				$buttons .= ' <a href="' . base_url('Controller_Orders/update/' . $value['id']) . '" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>';
			}
			if (in_array('deleteOrder', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-danger btn-sm" onclick="removeFunc(' . $value['id'] . ')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
			}

			$row = [];
			if (in_array('deleteOrder', $this->permission)) {
				$row[] = $checkbox;
			}
			$row[] = $value['bill_no'];
			$row[] = $value['customer_name'];
			$row[] = $value['customer_phone'];
			$row[] = 'UGX' . $value['net_amount'];
			$row[] = $buttons;

			$result['data'][$key] = $row;
		}

		return $this->response->setJSON($result);
	}

	public function returnToStock()
	{
		if (!in_array('returnOrder', $this->permission)) {
			return $this->response->setJSON(['success' => false, 'messages' => 'Permission denied']);
		}

		$orderId = $this->request->getPost('order_id');
		if (!$orderId) {
			return $this->response->setJSON(['success' => false, 'messages' => 'Order ID missing']);
		}

		$ordersModel = new Model_orders();
		$productsModel = new Model_products();
		$items = $ordersModel->getOrdersItemData($orderId);
		if (empty($items)) {
			return $this->response->setJSON(['success' => false, 'messages' => 'No order items found']);
		}

		$db = \Config\Database::connect();
		$itemsTable = $ordersModel->getItemsTableName();
		$db->transStart();

		$updated = 0;
		foreach ($items as $item) {
			$productId = (int) ($item['product_id'] ?? 0);
			if ($productId > 0) {
				$product = $productsModel->getProductData($productId);
				if (! empty($product)) {
					$productsModel->update(['availability' => 1], $productId);
					$updated++;
					continue;
				}
			}

			$productName = $item['product_name'] ?? null;
			if ($productName) {
				$newId = $productsModel->create([
					'name' => $productName,
					'imei' => $item['product_imei'] ?? null,
					'price' => $item['product_price'] ?? 0,
					'storage' => $item['product_storage'] ?? null,
					'ram' => $item['product_ram'] ?? null,
					'warehouse_id' => $item['product_warehouse_id'] ?? null,
					'availability' => 1,
					'date_added' => date('Y-m-d'),
				]);
				if ($newId) {
					if ($productId > 0) {
						$db->table($itemsTable)
							->where('order_id', (int) $orderId)
							->where('product_id', $productId)
							->update(['product_id' => (int) $newId]);
					}
					$updated++;
				}
			}
		}

		// Mark items as returned so products can be sold again.
		if ($db->fieldExists('returned', $itemsTable)) {
			$db->table($itemsTable)->where('order_id', (int) $orderId)->update(['returned' => 1]);
		}

		$db->transComplete();
		if (! $db->transStatus()) {
			return $this->response->setJSON(['success' => false, 'messages' => 'Return to stock failed']);
		}

		return $this->response->setJSON([
			'success' => true,
			'messages' => $updated . ' product(s) returned to stock',
		]);
	}

	public function create()
	{
		if (!in_array('createOrder', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$this->data['page_title'] = 'Add Order';

		$rules = ['product' => 'required'];

		$ordersModel = new Model_orders();
		$productsModel = new Model_products();
		$companyModel = new Model_company();

		if ($this->validate($rules)) {
			$productIds = $this->request->getPost('product');
			$duplicateImeis = $this->getDuplicateOrderImeis($productIds, $productsModel);
			if (!empty($duplicateImeis)) {
				session()->setFlashdata('errors', 'Duplicate IMEI in this order: ' . implode(', ', $duplicateImeis));
				return redirect()->to(base_url('Controller_Orders/create'));
			}

			$unavailableProducts = $this->getUnavailableProducts($productIds, $productsModel);
			if (!empty($unavailableProducts)) {
				session()->setFlashdata('errors', 'Product already sold: ' . implode(', ', $unavailableProducts));
				return redirect()->to(base_url('Controller_Orders/create'));
			}

			$payload = [
				'bill_no' => 'N0:' . date('YmdHis'),
				'customer_name' => $this->request->getPost('customer_name'),
				'customer_address' => $this->request->getPost('customer_address'),
				'customer_phone' => $this->request->getPost('customer_phone'),
				'date_time' => strtotime(date('Y-m-d h:i:s a')),
				'gross_amount' => $this->request->getPost('gross_amount_value'),
				'service_charge_rate' => $this->request->getPost('service_charge_rate'),
				'service_charge' => $this->request->getPost('service_charge_value') > 0 ? $this->request->getPost('service_charge_value') : 0,
				'vat_charge_rate' => $this->request->getPost('vat_charge_rate'),
				'vat_charge' => $this->request->getPost('vat_charge_value') > 0 ? $this->request->getPost('vat_charge_value') : 0,
				'net_amount' => $this->request->getPost('net_amount_value'),
				'discount' => $this->request->getPost('discount'),
				'paid_status' => 2,
				'user_id' => session()->get('id'),
			];

			$items = [];
			$rates = $this->request->getPost('rate_value');
			$amounts = $this->request->getPost('amount_value');
			if (is_array($productIds)) {
				foreach ($productIds as $index => $productId) {
					$items[] = [
						'product_id' => $productId,
						'rate' => $rates[$index] ?? 0,
						'amount' => $amounts[$index] ?? 0,
					];
				}
			}

			$orderId = $ordersModel->createOrder($payload, $items);
			if ($orderId) {
				session()->setFlashdata('success', 'Successfully created');
				return redirect()->to(base_url('Controller_Orders?print=' . $orderId));
			}

			session()->setFlashdata('errors', 'Error occurred!!');
			return redirect()->to(base_url('Controller_Orders/create'));
		}

		$company = $companyModel->getCompanyData(1);
		$this->data['company_data'] = $company ?: ['service_charge_value' => 0, 'vat_charge_value' => 0];
		$this->data['is_vat_enabled'] = !empty($company['vat_charge_value']);
		$this->data['is_service_enabled'] = !empty($company['service_charge_value']);

		$prefilledProduct = null;
		$productId = (int) $this->request->getGet('product_id');
		if ($productId) {
			$prefilledProduct = $productsModel->getProductData($productId);
		}

		$imei = $this->request->getGet('imei');
		if (!$prefilledProduct && $imei) {
			$prefilledProduct = $productsModel->getProductByIMEI($imei);
		}

		if ($prefilledProduct) {
			$this->data['products'] = [$prefilledProduct];
			$this->data['prefilled_product'] = $prefilledProduct;
		} else {
			$this->data['products'] = $productsModel->getActiveProductData();
		}

		return $this->render_template('orders/create', $this->data);
	}

	public function getProductValueById()
	{
		$productId = $this->request->getPost('product_id');
		if ($productId) {
			$productsModel = new Model_products();
			$productData = $productsModel->getProductData($productId);
			return $this->response->setJSON($productData);
		}

		return $this->response->setJSON([]);
	}

	public function getTableProductRow()
	{
		$productsModel = new Model_products();
		$products = $productsModel->getActiveProductData();
		return $this->response->setJSON($products);
	}

	public function update($id)
	{
		if (!in_array('updateOrder', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		if (!$id) {
			return redirect()->to(base_url('dashboard'));
		}

		$this->data['page_title'] = 'Update Order';

		$rules = ['product' => 'required'];
		$ordersModel = new Model_orders();
		$productsModel = new Model_products();
		$companyModel = new Model_company();

		if ($this->validate($rules)) {
			$productIds = $this->request->getPost('product');
			$duplicateImeis = $this->getDuplicateOrderImeis($productIds, $productsModel);
			if (!empty($duplicateImeis)) {
				session()->setFlashdata('errors', 'Duplicate IMEI in this order: ' . implode(', ', $duplicateImeis));
				return redirect()->to(base_url('Controller_Orders/update/' . $id));
			}

			$existingItems = $ordersModel->getOrdersItemData($id) ?: [];
			$existingProductIds = array_values(array_filter(array_column($existingItems, 'product_id')));
			$unavailableProducts = $this->getUnavailableProducts($productIds, $productsModel, $existingProductIds, $id);
			if (!empty($unavailableProducts)) {
				session()->setFlashdata('errors', 'Product already sold: ' . implode(', ', $unavailableProducts));
				return redirect()->to(base_url('Controller_Orders/update/' . $id));
			}

			$payload = [
				'customer_name' => $this->request->getPost('customer_name'),
				'customer_address' => $this->request->getPost('customer_address'),
				'customer_phone' => $this->request->getPost('customer_phone'),
				'gross_amount' => $this->request->getPost('gross_amount_value'),
				'service_charge_rate' => $this->request->getPost('service_charge_rate'),
				'service_charge' => $this->request->getPost('service_charge_value') > 0 ? $this->request->getPost('service_charge_value') : 0,
				'vat_charge_rate' => $this->request->getPost('vat_charge_rate'),
				'vat_charge' => $this->request->getPost('vat_charge_value') > 0 ? $this->request->getPost('vat_charge_value') : 0,
				'net_amount' => $this->request->getPost('net_amount_value'),
				'discount' => $this->request->getPost('discount'),
				'paid_status' => $this->request->getPost('paid_status'),
				'user_id' => session()->get('id'),
			];

			$items = [];
			$rates = $this->request->getPost('rate_value');
			$amounts = $this->request->getPost('amount_value');
			if (is_array($productIds)) {
				foreach ($productIds as $index => $productId) {
					$items[] = [
						'product_id' => $productId,
						'rate' => $rates[$index] ?? 0,
						'amount' => $amounts[$index] ?? 0,
					];
				}
			}

			$update = $ordersModel->updateOrder($id, $payload, $items, $existingItems);
			if ($update) {
				session()->setFlashdata('success', 'Successfully updated');
				return redirect()->to(base_url('Controller_Orders/update/' . $id));
			}

			session()->setFlashdata('errors', 'Error occurred!!');
			return redirect()->to(base_url('Controller_Orders/update/' . $id));
		}

		$company = $companyModel->getCompanyData(1);
		$this->data['company_data'] = $company ?: ['service_charge_value' => 0, 'vat_charge_value' => 0];
		$this->data['is_vat_enabled'] = !empty($company['vat_charge_value']);
		$this->data['is_service_enabled'] = !empty($company['service_charge_value']);

		$ordersData = $ordersModel->getOrdersData($id);
		$result = ['order' => $ordersData, 'order_item' => []];
		if (!empty($ordersData['id'])) {
			$ordersItem = $ordersModel->getOrdersItemData($ordersData['id']);
			foreach ($ordersItem as $item) {
				$result['order_item'][] = $item;
			}
		}

		$this->data['order_data'] = $result;
		$this->data['products'] = $productsModel->getActiveProductData();

		return $this->render_template('orders/edit', $this->data);
	}

	public function remove()
	{
		if (!in_array('deleteOrder', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$orderId = $this->request->getPost('order_id');
		$response = [];
		if ($orderId) {
			$ordersModel = new Model_orders();
			$delete = $ordersModel->remove($orderId);
			if ($delete) {
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

		return redirect()->to(base_url('Controller_Orders'));
	}

	public function bulkRemove()
	{
		if (!in_array('deleteOrder', $this->permission)) {
			return $this->response->setJSON(['success' => false, 'messages' => 'Permission denied']);
		}

		$orderIds = $this->request->getPost('order_ids');
		$response = [];

		if (!empty($orderIds) && is_array($orderIds)) {
			$ordersModel = new Model_orders();
			$deleted = 0;
			foreach ($orderIds as $id) {
				$id = (int) $id;
				if ($id > 0 && $ordersModel->remove($id)) {
					$deleted++;
				}
			}
			$response['success'] = true;
			$response['messages'] = $deleted . ' order(s) deleted successfully';
		} else {
			$response['success'] = false;
			$response['messages'] = 'No orders selected';
		}

		return $this->response->setJSON($response);
	}

	public function getProductByIMEI()
	{
		$imei = $this->request->getPost('imei');
		if ($imei) {
			$productsModel = new Model_products();
			$product = $productsModel->getProductByIMEI($imei);
			if ($product) {
				return $this->response->setJSON(['status' => 'success', 'data' => $product]);
			}

			return $this->response->setJSON(['status' => 'error', 'message' => 'IMEI not found']);
		}

		return $this->response->setJSON(['status' => 'error', 'message' => 'IMEI missing']);
	}

	public function printDiv($id)
	{
		if (!in_array('printOrder', $this->permission) && !in_array('viewOrder', $this->permission) && !in_array('createOrder', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		if (!$id) {
			return redirect()->to(base_url('dashboard'));
		}

		$ordersModel = new Model_orders();
		$productsModel = new Model_products();
		$companyModel = new Model_company();

		$orderData = $ordersModel->getOrdersData($id);
		$ordersItems = $ordersModel->getOrdersItemData($id);
		$companyInfo = $companyModel->getCompanyData(1);

		$orderDate = Time::createFromTimestamp((int) ($orderData['date_time'] ?? 0), app_timezone())
			->format('d/m/Y H:i');
		$servedBy = session()->get('username');
		$autoPrint = $this->request->getGet('auto') === '1';
		$embed = $this->request->getGet('embed') === '1';
		$showActions = !$embed && !$autoPrint;

		$html = $this->buildReceiptHtml($orderData, $ordersItems, $productsModel, $companyInfo, $orderDate, $servedBy, $autoPrint, '', $showActions);
		return $this->response->setBody($html);
	}

	public function downloadPDF($id)
	{
		if (!in_array('printOrder', $this->permission) && !in_array('viewOrder', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		if (!$id) {
			return redirect()->to(base_url('dashboard'));
		}

		if (!class_exists('Dompdf\\Dompdf')) {
			return $this->response->setStatusCode(500)->setBody('PDF library not installed.');
		}

		$ordersModel = new Model_orders();
		$productsModel = new Model_products();
		$companyModel = new Model_company();

		$orderData = $ordersModel->getOrdersData($id);
		$ordersItems = $ordersModel->getOrdersItemData($id);
		$companyInfo = $companyModel->getCompanyData(1);

		$orderDate = Time::createFromTimestamp((int) ($orderData['date_time'] ?? 0), app_timezone())
			->format('d/m/Y H:i');
		$servedBy = session()->get('username');

		$html = $this->buildReceiptHtml($orderData, $ordersItems, $productsModel, $companyInfo, $orderDate, $servedBy, false, '', false);

		$options = new \Dompdf\Options();
		$options->set('isRemoteEnabled', true);
		$options->set('defaultFont', 'sans-serif');

		$dompdf = new \Dompdf\Dompdf($options);
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A5', 'portrait');
		$dompdf->render();

		$filename = 'Receipt_' . $orderData['bill_no'] . '.pdf';
		$dompdf->stream($filename, ['Attachment' => true]);
		return;
	}

	private function buildReceiptHtml(array $orderData, array $ordersItems, Model_products $productsModel, $companyInfo, string $orderDate, string $servedBy, bool $autoPrint = true, string $returnUrl = '', bool $showActions = true)
	{
		$logoUrl = base_url('assets/images/product_image/chloe2.png');
		$returnJs = '';
		if ($autoPrint && !empty($returnUrl)) {
			$returnJs = '<script>window.onafterprint = function(){ window.location.href = "' . $returnUrl . '"; };</script>';
		}

		$embedCss = '';
		if (!$showActions) {
			$embedCss = 'body { background: #fff; padding: 0; } .invoice { margin: 0 auto; max-width: 100%; border-radius: 0; }';
		}

		$html = '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Receipt</title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<style>
* { box-sizing: border-box; }
body { font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif; color: #1f2937; background: #e5e7eb; margin: 0; padding: 16px; }
.invoice { max-width: 520px; width: 100%; margin: 24px auto; padding: 24px 22px; border-radius: 12px; border: 1px solid #cbd5e1; background: #ffffff; box-shadow: 0 8px 26px rgba(15,23,42,0.10); }
.invoice-header { text-align: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 2px solid #0f4c81; }
.brand { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.brand-logo { width: 62px; height: 62px; object-fit: contain; border-radius: 10px; border: 1px solid #cbd5e1; padding: 5px; background: #fff; }
.brand-text h1 { text-transform: uppercase; font-size: 20px; font-weight: 700; letter-spacing: 0.6px; color: #0f4c81; margin: 0 0 2px 0; }
.brand-text p { font-size: 12px; color: #475569; margin: 0; }
.receipt-date { margin-top: 8px; font-size: 12px; color: #334155; }
.invoice-meta, .invoice-items, .invoice-totals { margin-bottom: 16px; }
.section-title { display: block; font-size: 12px; font-weight: 700; color: #0f4c81; margin: 8px 0; text-transform: uppercase; letter-spacing: 0.5px; }
.invoice-meta div { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 4px; }
.invoice-meta div span:first-child { color: #64748b; }
.invoice-items table { width: 100%; border-collapse: collapse; font-size: 14px; }
.invoice-items th, .invoice-items td { padding: 8px 6px; }
.invoice-items th { border-bottom: 2px solid #0f4c81; text-transform: uppercase; font-weight: 700; text-align: left; background: #e8f0f8; color: #0f4c81; font-size: 12px; letter-spacing: 0.3px; }
.invoice-items td { border-bottom: 1px solid #e2e8f0; vertical-align: top; }
.invoice-items td .small { font-size: 12px; color: #64748b; }
.imei-code { font-size: 14px; font-weight: 600; color: #1f2937; display: inline-block; margin-top: 2px; }
.invoice-totals div { display: flex; justify-content: space-between; font-weight: 700; font-size: 18px; color: #0f4c81; border-top: 2px solid #0f4c81; padding-top: 10px; }
.invoice-footer { text-align: center; font-size: 13px; margin-top: 20px; color: #64748b; }
.invoice-footer .served-by { margin-top: 10px; padding: 7px 14px; background: #e8f0f8; border-radius: 6px; display: inline-block; font-size: 12px; font-weight: 600; color: #0f4c81; }
@media print {
body { background: #fff; }
.invoice { border: none; box-shadow: none; }
.receipt-actions { display: none; }
}
@media (max-width: 640px) {
body { padding: 10px; }
.invoice { margin: 12px auto; padding: 16px 14px; }
.brand-text h1 { font-size: 18px; }
.invoice-meta div { font-size: 13px; }
.invoice-items th, .invoice-items td { padding: 6px 4px; font-size: 13px; }
.invoice-totals div { font-size: 16px; }
}
.receipt-actions { text-align: center; margin: 20px auto; max-width: 520px; }
.receipt-actions .btn { display: inline-block; padding: 10px 24px; margin: 0 6px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; color: #fff; }
.receipt-actions .btn-print { background: #0f4c81; }
.receipt-actions .btn-save { background: #0f766e; }
.receipt-actions .btn-icon { width: 16px; height: 16px; vertical-align: -3px; margin-right: 6px; fill: currentColor; }
.receipt-actions .btn:hover { opacity: 0.9; }
' . $embedCss . '
</style>
</head>
<body>

' . ($showActions ? '<div class="receipt-actions">
<button class="btn btn-primary btn-sm" onclick="window.print();">Print Receipt</button>
<button class="btn btn-save" type="button" onclick="saveReceiptImage();">
<svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h11l3 3v15H5zM7 5v4h8V5zM7 19h10v-6H7z"></path></svg>
Save Image
</button>
</div>' : '') . '

<div class="invoice" id="receiptCard" data-receipt="' . htmlspecialchars((string) ($orderData['bill_no'] ?? '')) . '">
<div class="invoice-header">
<div class="brand">
<img src="' . $logoUrl . '" alt="Company Logo" class="brand-logo">
<div class="brand-text">
<h1>Chloe Phone Center</h1>
<p>Sales Receipt</p>
<p>chloephonecenter@gmail.com</p>
</div>
</div>
<div class="receipt-date">Date: ' . $orderDate . '</div>
</div>

<div class="invoice-meta">
<div><span>Receipt No:</span> <span>' . $orderData['bill_no'] . '</span></div>
<div class="section-title">Customer Details</div>
<div><span>Customer:</span> <span>' . $orderData['customer_name'] . '</span></div>
<div><span>Contact:</span> <span>' . $orderData['customer_phone'] . '</span></div>
<div><span>NIN No:</span> <span>' . $orderData['customer_address'] . '</span></div>
</div>

<div class="invoice-items">
<table>
<thead>
<tr>
<th>Item Description</th>
<th class="text-right">Qty</th>
<th class="text-right">Amount</th>
</tr>
</thead>
<tbody>';

		foreach ($ordersItems as $item) {
			$product = [];
			if (empty($item['product_name']) && ! empty($item['product_id'])) {
				$product = $productsModel->getProductData($item['product_id']);
			}

			$productName = $item['product_name'] ?? ($product['name'] ?? '');
			$ramValue = $item['product_ram'] ?? ($product['ram'] ?? '');
			$storageValue = $item['product_storage'] ?? ($product['storage'] ?? '');
			$imei = $item['product_imei'] ?? ($product['imei'] ?? '');

			$ramLine = '';
			$storageLine = '';
			if ($ramValue !== '') {
				$ramLine = '<small>RAM: ' . htmlspecialchars($ramValue) . ' GB</small><br>';
			}
			if ($storageValue !== '') {
				$storageLine = '<small>Storage: ' . htmlspecialchars($storageValue) . ' GB</small><br>';
			}

			$html .= '<tr>
<td>
<strong>' . htmlspecialchars($productName) . '</strong><br>
' . $ramLine . $storageLine . '
<span class="imei-code">IMEI: ' . htmlspecialchars($imei) . '</span>
</td>
<td class="text-right">01</td>
<td class="text-right">' . number_format((float) $item['amount'], 0) . ' UGX</td>
</tr>';
		}

		$html .= '</tbody>
</table>
</div>

<div class="invoice-totals">
<div><span>Total:</span> <span>' . number_format((float) $orderData['net_amount'], 0) . ' UGX</span></div>
</div>

<div class="invoice-footer">
<p>Thank you for your business!</p>
' . (!empty($servedBy) ? '<div class="served-by">Served by: ' . htmlspecialchars($servedBy) . '</div>' : '') . '
</div>
</div>

' . $returnJs . '
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
(function() {
	function downloadDataUrl(dataUrl, filename) {
		var link = document.createElement("a");
		link.href = dataUrl;
		link.download = filename;
		document.body.appendChild(link);
		link.click();
		link.remove();
	}

	window.saveReceiptImage = function() {
		var target = document.getElementById("receiptCard");
		if (!target || typeof html2canvas !== "function") {
			return;
		}

		var receiptNo = target.getAttribute("data-receipt") || "Receipt";
		var fileName = "Receipt_" + receiptNo + ".png";

		html2canvas(target, { scale: 2, backgroundColor: "#ffffff" }).then(function(canvas) {
			downloadDataUrl(canvas.toDataURL("image/png"), fileName);
		});
	};
})();
</script>
</body>
</html>';

		return $html;
	}

	private function getDuplicateOrderImeis($productIds, Model_products $productsModel): array
	{
		if (!is_array($productIds) || empty($productIds)) {
			return [];
		}

		$normalizedIds = [];
		foreach ($productIds as $productId) {
			$productId = (int) $productId;
			if ($productId > 0) {
				$normalizedIds[] = $productId;
			}
		}

		if (empty($normalizedIds)) {
			return [];
		}

		$idCounts = array_count_values($normalizedIds);
		$duplicateImeis = [];

		foreach ($idCounts as $productId => $count) {
			if ($count > 1) {
				$product = $productsModel->getProductData($productId);
				if (!empty($product['imei'])) {
					$duplicateImeis[] = $product['imei'];
				} else {
					$duplicateImeis[] = 'Product ID ' . $productId;
				}
			}
		}

		return $duplicateImeis;
	}

	private function getUnavailableProducts($productIds, Model_products $productsModel, array $allowedProductIds = [], $currentOrderId = null): array
	{
		if (!is_array($productIds) || empty($productIds)) {
			return [];
		}

		$allowedLookup = array_flip(array_map('intval', $allowedProductIds));
		$unavailable = [];
		$checked = [];
		$db = \Config\Database::connect();
		$itemsTable = $db->tableExists('orders_item') ? 'orders_item' : 'order_items';
		$hasReturnFlag = $db->fieldExists('returned', $itemsTable);

		foreach ($productIds as $productId) {
			$productId = (int) $productId;
			if ($productId <= 0 || isset($checked[$productId]) || isset($allowedLookup[$productId])) {
				continue;
			}
			$checked[$productId] = true;

			$ordersQuery = $db->table($itemsTable)->select('order_id')->where('product_id', $productId);
			if ($hasReturnFlag) {
				$ordersQuery->where('returned', 0);
			}
			if (!empty($currentOrderId)) {
				$ordersQuery->where('order_id !=', (int) $currentOrderId);
			}
			$existingOrder = $ordersQuery->get()->getRowArray();
			if ($existingOrder) {
				$product = $productsModel->getProductData($productId);
				if (!empty($product['imei'])) {
					$unavailable[] = $product['imei'];
				} elseif (!empty($product['name'])) {
					$unavailable[] = $product['name'];
				} else {
					$unavailable[] = 'Product ID ' . $productId;
				}
				continue;
			}

			$product = $productsModel->getProductData($productId);
			if ($product && (int) ($product['availability'] ?? 0) !== 1) {
				if (!empty($product['imei'])) {
					$unavailable[] = $product['imei'];
				} elseif (!empty($product['name'])) {
					$unavailable[] = $product['name'];
				} else {
					$unavailable[] = 'Product ID ' . $productId;
				}
			}
		}

		return $unavailable;
	}
}
