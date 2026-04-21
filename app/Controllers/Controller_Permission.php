<?php

namespace App\Controllers;

use App\Models\Model_groups;

class Controller_Permission extends Admin_Controller
{
    public function __construct()
    {
        $this->not_logged_in();
        $this->data['page_title'] = 'Permission';
    }

    public function index()
    {
        if (!in_array('viewGroup', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $groupsModel = new Model_groups();
        $this->data['groups_data'] = $groupsModel->getGroupData();
        return $this->render_template('permission/index', $this->data);
    }

    public function create()
    {
        if (!in_array('createGroup', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $rules = ['group_name' => 'required'];
        $groupsModel = new Model_groups();

        if ($this->validate($rules)) {
            $permission = serialize($this->request->getPost('permission'));
            $data = [
                'group_name' => $this->request->getPost('group_name'),
                'permission' => $permission,
            ];

            $create = $groupsModel->create($data);
            if ($create) {
                session()->setFlashdata('success', 'Successfully created');
                return redirect()->to(base_url('Controller_Permission'));
            }

            session()->setFlashdata('errors', 'Error occurred!!');
            return redirect()->to(base_url('Controller_Permission/create'));
        }

        return $this->render_template('permission/create', $this->data);
    }

    public function edit($id = null)
    {
        if (!in_array('updateGroup', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        if ($id) {
            $rules = ['group_name' => 'required'];
            $groupsModel = new Model_groups();

            if ($this->validate($rules)) {
                $permission = serialize($this->request->getPost('permission'));
                $data = [
                    'group_name' => $this->request->getPost('group_name'),
                    'permission' => $permission,
                ];

                $update = $groupsModel->edit($data, $id);
                if ($update) {
                    session()->setFlashdata('success', 'Successfully updated');
                    return redirect()->to(base_url('Controller_Permission'));
                }

                session()->setFlashdata('errors', 'Error occurred!!');
                return redirect()->to(base_url('Controller_Permission/edit/' . $id));
            }

            $this->data['group_data'] = $groupsModel->getGroupData($id);
            return $this->render_template('permission/edit', $this->data);
        }

        return redirect()->to(base_url('Controller_Permission'));
    }

    public function delete($id)
    {
        if (!in_array('deleteGroup', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        if ($id) {
            $groupsModel = new Model_groups();
            if ($this->request->getPost('confirm')) {
                $check = $groupsModel->existInUserGroup($id);
                if ($check) {
                    session()->setFlashdata('error', 'Group exists in the users');
                    return redirect()->to(base_url('Controller_Permission'));
                }

                $delete = $groupsModel->deleteGroup($id);
                if ($delete) {
                    session()->setFlashdata('success', 'Successfully removed');
                    return redirect()->to(base_url('Controller_Permission'));
                }

                session()->setFlashdata('error', 'Error occurred!!');
                return redirect()->to(base_url('Controller_Permission/delete/' . $id));
            }

            $this->data['id'] = $id;
            return $this->render_template('permission/delete', $this->data);
        }

        return redirect()->to(base_url('Controller_Permission'));
    }
}
