<?php

namespace App\Controllers;

use App\Models\Model_groups;
use App\Models\Model_stores;
use App\Models\Model_users;

class Controller_Members extends Admin_Controller
{
    public function __construct()
    {
        $this->not_logged_in();
        $this->data['page_title'] = 'Members';
    }

    public function index()
    {
        if (!in_array('viewUser', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $usersModel = new Model_users();
        $storesModel = new Model_stores();
        $userData = $usersModel->getUserData();

        $result = [];
        foreach ($userData as $k => $v) {
            $result[$k]['user_info'] = $v;
            $group = $usersModel->getUserGroup($v['id']);
            $result[$k]['user_group'] = $group;

            $warehouse = null;
            $warehouses = $storesModel->getStoresData();
            foreach ($warehouses as $store) {
                if (!empty($store['assigned_user_id']) && (int) $store['assigned_user_id'] === (int) $v['id']) {
                    $warehouse = $store;
                    break;
                }
            }
            $result[$k]['warehouse'] = $warehouse;
        }

        $this->data['user_data'] = $result;
        return $this->render_template('members/index', $this->data);
    }

    public function create()
    {
        if (!in_array('createUser', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $rules = [
            'groups' => 'required',
            'warehouse_id' => 'permit_empty|integer',
            'username' => 'required|min_length[5]|max_length[12]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'cpassword' => 'required|matches[password]',
            'fname' => 'required',
        ];

        $usersModel = new Model_users();
        $groupsModel = new Model_groups();
        $storesModel = new Model_stores();

        if ($this->validate($rules)) {
            $password = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
            $data = [
                'username' => $this->request->getPost('username'),
                'password' => $password,
                'email' => $this->request->getPost('email'),
                'firstname' => $this->request->getPost('fname'),
                'lastname' => $this->request->getPost('lname'),
                'phone' => $this->request->getPost('phone'),
                'gender' => $this->request->getPost('gender'),
            ];

            $create = $usersModel->create($data, $this->request->getPost('groups'));
            if ($create) {
                $warehouseId = $this->request->getPost('warehouse_id');
                if ($warehouseId) {
                    $storesModel->update(['assigned_user_id' => $create], $warehouseId);
                }
                session()->setFlashdata('success', 'Successfully created');
                return redirect()->to(base_url('Controller_Members'));
            }

            session()->setFlashdata('errors', 'Error occurred!!');
            return redirect()->to(base_url('Controller_Members/create'));
        }

        $this->data['group_data'] = $groupsModel->getGroupData();
        $this->data['warehouses'] = $storesModel->getStoresData();
        return $this->render_template('members/create', $this->data);
    }

    public function edit($id = null)
    {
        if (!in_array('updateUser', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $usersModel = new Model_users();
        $groupsModel = new Model_groups();
        $storesModel = new Model_stores();
        $this->data['warehouses'] = $storesModel->getStoresData();

        if ($id) {
            $rules = [
                'groups' => 'required',
                'username' => 'required|min_length[5]|max_length[12]',
                'email' => 'required|valid_email',
                'fname' => 'required',
            ];

            if ($this->validate($rules)) {
                if (empty($this->request->getPost('password')) && empty($this->request->getPost('cpassword'))) {
                    $data = [
                        'username' => $this->request->getPost('username'),
                        'email' => $this->request->getPost('email'),
                        'firstname' => $this->request->getPost('fname'),
                        'lastname' => $this->request->getPost('lname'),
                        'phone' => $this->request->getPost('phone'),
                        'gender' => $this->request->getPost('gender'),
                    ];

                    $update = $usersModel->edit($data, $id, $this->request->getPost('groups'));
                    if ($update) {
                        $warehouseId = $this->request->getPost('warehouse_id');
                        if ($warehouseId) {
                            $storesModel->update(['assigned_user_id' => $id], $warehouseId);
                        }
                        session()->setFlashdata('success', 'Successfully updated');
                        return redirect()->to(base_url('Controller_Members'));
                    }

                    session()->setFlashdata('errors', 'Error occurred!!');
                    return redirect()->to(base_url('Controller_Members/edit/' . $id));
                }

                $rulesPassword = [
                    'password' => 'required|min_length[8]',
                    'cpassword' => 'required|matches[password]',
                ];

                if ($this->validate($rulesPassword)) {
                    $password = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                    $data = [
                        'username' => $this->request->getPost('username'),
                        'password' => $password,
                        'email' => $this->request->getPost('email'),
                        'firstname' => $this->request->getPost('fname'),
                        'lastname' => $this->request->getPost('lname'),
                        'phone' => $this->request->getPost('phone'),
                        'gender' => $this->request->getPost('gender'),
                    ];

                    $update = $usersModel->edit($data, $id, $this->request->getPost('groups'));
                    if ($update) {
                        $warehouseId = $this->request->getPost('warehouse_id');
                        if ($warehouseId) {
                            $storesModel->update(['assigned_user_id' => $id], $warehouseId);
                        }
                        session()->setFlashdata('success', 'Successfully updated');
                        return redirect()->to(base_url('Controller_Members'));
                    }

                    session()->setFlashdata('errors', 'Error occurred!!');
                    return redirect()->to(base_url('Controller_Members/edit/' . $id));
                }
            }

            $this->data['user_data'] = $usersModel->getUserData($id);
            $this->data['user_group'] = $usersModel->getUserGroup($id);
            $this->data['group_data'] = $groupsModel->getGroupData();
            return $this->render_template('members/edit', $this->data);
        }

        return redirect()->to(base_url('Controller_Members'));
    }

    public function delete($id)
    {
        if (!in_array('deleteUser', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        if ($id) {
            if ($this->request->getPost('confirm')) {
                $usersModel = new Model_users();
                $delete = $usersModel->deleteUser($id);
                if ($delete) {
                    session()->setFlashdata('success', 'Successfully removed');
                    return redirect()->to(base_url('Controller_Members'));
                }

                session()->setFlashdata('error', 'Error occurred!!');
                return redirect()->to(base_url('Controller_Members/delete/' . $id));
            }

            $this->data['id'] = $id;
            return $this->render_template('members/delete', $this->data);
        }

        return redirect()->to(base_url('Controller_Members'));
    }

    public function profile()
    {
        if (!in_array('viewProfile', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $userId = session()->get('id');
        $usersModel = new Model_users();

        $this->data['user_data'] = $usersModel->getUserData($userId);
        $this->data['user_group'] = $usersModel->getUserGroup($userId);

        return $this->render_template('members/profile', $this->data);
    }

    public function setting()
    {
        if (!in_array('updateSetting', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $id = session()->get('id');
        if (!$id) {
            return redirect()->to(base_url('dashboard'));
        }

        $usersModel = new Model_users();
        $groupsModel = new Model_groups();

        $rules = [
            'username' => 'required|min_length[5]|max_length[12]',
            'email' => 'required|valid_email',
            'fname' => 'required',
        ];

        if ($this->validate($rules)) {
            if (empty($this->request->getPost('password')) && empty($this->request->getPost('cpassword'))) {
                $data = [
                    'username' => $this->request->getPost('username'),
                    'email' => $this->request->getPost('email'),
                    'firstname' => $this->request->getPost('fname'),
                    'lastname' => $this->request->getPost('lname'),
                    'phone' => $this->request->getPost('phone'),
                    'gender' => $this->request->getPost('gender'),
                ];

                $update = $usersModel->edit($data, $id);
                if ($update) {
                    session()->setFlashdata('success', 'Successfully updated');
                    return redirect()->to(base_url('Controller_Members/setting'));
                }

                session()->setFlashdata('errors', 'Error occurred!!');
                return redirect()->to(base_url('Controller_Members/setting'));
            }

            $passwordRules = [
                'password' => 'required|min_length[8]',
                'cpassword' => 'required|matches[password]',
            ];

            if ($this->validate($passwordRules)) {
                $password = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                $data = [
                    'username' => $this->request->getPost('username'),
                    'password' => $password,
                    'email' => $this->request->getPost('email'),
                    'firstname' => $this->request->getPost('fname'),
                    'lastname' => $this->request->getPost('lname'),
                    'phone' => $this->request->getPost('phone'),
                    'gender' => $this->request->getPost('gender'),
                ];

                $update = $usersModel->edit($data, $id, $this->request->getPost('groups'));
                if ($update) {
                    session()->setFlashdata('success', 'Successfully updated');
                    return redirect()->to(base_url('Controller_Members/setting'));
                }

                session()->setFlashdata('errors', 'Error occurred!!');
                return redirect()->to(base_url('Controller_Members/setting'));
            }
        }

        $this->data['user_data'] = $usersModel->getUserData($id);
        $this->data['user_group'] = $usersModel->getUserGroup($id);
        $this->data['group_data'] = $groupsModel->getGroupData();

        return $this->render_template('members/setting', $this->data);
    }
}
