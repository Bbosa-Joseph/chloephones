<?php

namespace App\Controllers;

use App\Models\Model_stores;
use App\Models\Model_users;

class Controller_Warehouse extends Admin_Controller
{
	public function __construct()
	{

		$this->not_logged_in();
		$this->data['page_title'] = 'Warehouse';
	}

	public function index()
	{
		if (!in_array('viewStore', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$usersModel = new Model_users();
		$this->data['users'] = $usersModel->getUserData();
		return $this->render_template('warehouse/index', $this->data);
	}

	public function fetchStoresDataById($id)
	{
		if ($id) {
			$storesModel = new Model_stores();
			$data = $storesModel->getStoresData($id);
			return $this->response->setJSON($data);
		}

		return $this->response->setJSON([]);
	}

	public function fetchStoresData()
	{
		$result = ['data' => []];
		$storesModel = new Model_stores();
		$usersModel = new Model_users();
		$db = \Config\Database::connect();

		$totals = $db->table('products')
			->select('warehouse_id, COUNT(*) as total_stock, SUM(price) as total_value')
			->where('availability', 1)
			->groupBy('warehouse_id')
			->get()
			->getResultArray();
		$totalsByWarehouse = [];
		foreach ($totals as $row) {
			$totalsByWarehouse[(int) $row['warehouse_id']] = [
				'total_stock' => (int) ($row['total_stock'] ?? 0),
				'total_value' => (float) ($row['total_value'] ?? 0),
			];
		}

		$data = $storesModel->getStoresData();
		foreach ($data as $key => $value) {
			$buttons = '';
			if (in_array('updateStore', $this->permission)) {
				$buttons = '<button type="button" class="btn btn-warning btn-sm" onclick="editFunc(' . $value['id'] . ')" data-toggle="modal" data-target="#editModal"><i class="fa fa-pencil"></i></button>';
			}
			if (in_array('deleteStore', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-danger btn-sm" onclick="removeFunc(' . $value['id'] . ')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
			}

			$status = ((int) $value['active'] === 1)
				? '<span class="label label-success">Active</span>'
				: '<span class="label label-warning">Inactive</span>';

			$assignedUser = '';
			if (!empty($value['assigned_user_id'])) {
				$user = $usersModel->getUserData($value['assigned_user_id']);
				$assignedUser = $user ? htmlspecialchars($user['username'] ?? $user['email']) : '';
			}

			$summary = $totalsByWarehouse[(int) $value['id']] ?? ['total_stock' => 0, 'total_value' => 0];
			$result['data'][$key] = [
				$value['name'],
				$assignedUser,
				$summary['total_stock'],
				'UGX ' . number_format($summary['total_value']),
				$status,
				$buttons,
			];
		}

		return $this->response->setJSON($result);
	}

	public function create()
	{
		if (!in_array('createStore', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$response = [];
		$rules = [
			'store_name' => 'required',
			'active' => 'required',
			'assigned_user_id' => 'permit_empty|integer',
		];

		if ($this->validate($rules)) {
			$storesModel = new Model_stores();

			$assignedUser = $this->request->getPost('assigned_user_id');
			if ($assignedUser) {
				$existing = $storesModel->getStoreByUserId($assignedUser);
				if ($existing) {
					$response['success'] = false;
					$response['messages'] = 'This member is already assigned to warehouse "' . $existing['name'] . '". A member can only be assigned to one warehouse.';
					return $this->response->setJSON($response);
				}
			}

			$data = [
				'name' => $this->request->getPost('store_name'),
				'active' => $this->request->getPost('active'),
				'assigned_user_id' => $assignedUser ?: null,
			];

			$create = $storesModel->create($data);
			if ($create) {
				$response['success'] = true;
				$response['messages'] = 'Successfully created';
			} else {
				$response['success'] = false;
				$response['messages'] = 'Error in the database while creating the warehouse information';
			}
		} else {
			$response['success'] = false;
			$response['messages'] = $this->validator->getErrors();
		}

		return $this->response->setJSON($response);
	}

	public function update($id)
	{
		if (!in_array('updateStore', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$response = [];
		if ($id) {
			$rules = [
				'edit_store_name' => 'required',
				'edit_active' => 'required',
				'edit_assigned_user_id' => 'permit_empty|integer',
			];

			if ($this->validate($rules)) {
				$storesModel = new Model_stores();

				$assignedUser = $this->request->getPost('edit_assigned_user_id');
				if ($assignedUser) {
					$existing = $storesModel->getStoreByUserId($assignedUser, $id);
					if ($existing) {
						$response['success'] = false;
						$response['messages'] = 'This member is already assigned to warehouse "' . $existing['name'] . '". A member can only be assigned to one warehouse.';
						return $this->response->setJSON($response);
					}
				}

				$data = [
					'name' => $this->request->getPost('edit_store_name'),
					'active' => $this->request->getPost('edit_active'),
					'assigned_user_id' => $assignedUser ?: null,
				];

				$update = $storesModel->update($data, $id);
				if ($update) {
					$response['success'] = true;
					$response['messages'] = 'Successfully updated';
				} else {
					$response['success'] = false;
					$response['messages'] = 'Error in the database while updating the warehouse information';
				}
			} else {
				$response['success'] = false;
				$response['messages'] = $this->validator->getErrors();
			}
		} else {
			$response['success'] = false;
			$response['messages'] = 'Error please refresh the page again!!';
		}

		return $this->response->setJSON($response);
	}

	public function remove()
	{
		if (!in_array('deleteStore', $this->permission)) {
			return redirect()->to(base_url('dashboard'));
		}

		$storeId = $this->request->getPost('store_id');
		$response = [];
		if ($storeId) {
			$storesModel = new Model_stores();
			$delete = $storesModel->remove($storeId);
			if ($delete) {
				$response['success'] = true;
				$response['messages'] = 'Successfully removed';
			} else {
				$response['success'] = false;
				$response['messages'] = 'Error in the database while removing the warehouse information';
			}
		} else {
			$response['success'] = false;
			$response['messages'] = 'Refresh the page again!!';
		}

		return $this->response->setJSON($response);
	}
}
