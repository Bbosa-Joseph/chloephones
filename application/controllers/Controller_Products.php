<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Controller_Products extends Admin_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Products';

		$this->load->model('model_products');
		$this->load->model('model_brands');
		$this->load->model('model_category');
		$this->load->model('model_stores');
		$this->load->model('model_attributes');
		$this->load->model('model_notifications');
	}

    /* 
    * It only redirects to the manage product page
    */
	public function index()
	{
        if(!in_array('viewProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->data['warehouses'] = $this->model_stores->getStoresData();
		$this->render_template('products/index', $this->data);	
	}

    /*
    * It Fetches the products data from the product table 
    * this function is called from the datatable ajax function
    */
public function fetchProductData()
{
    $result = array('data' => array());
    try {
        $data = $this->model_products->getProductData();
        if (!is_array($data)) $data = array();
        foreach ($data as $key => $value) {
            $buttons = '';
            if(in_array('updateProduct', $this->permission)) {
                $buttons .= '<a href="'.base_url('Controller_Products/update/'.$value['id']).'" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>';
            }
            if(in_array('deleteProduct', $this->permission)) { 
                $buttons .= ' <button type="button" class="btn btn-danger btn-sm" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
            }
            $availability = ($value['availability'] == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';
            if (isset($value['warehouse_id']) && $value['warehouse_id']) {
                $store = $this->model_stores->getStoresData($value['warehouse_id']);
                $warehouse_name = $store ? $store['name'] : 'Unassigned';
            } else {
                $warehouse_name = 'Unassigned';
            }
            $result['data'][$key] = array(
                'id'           => $value['id'],
                'name'         => $value['name'],
                'imei'         => $value['imei'],
                'price'        => 'UGX'.$value['price'],
                'warehouse'    => $warehouse_name,
                'availability' => $availability,
                'date_added'   => isset($value['date_added']) ? $value['date_added'] : '',
                'stock_status' => '',
                'actions'      => $buttons
            );
        }
        echo json_encode($result);
    } catch (Exception $e) {
        error_log('fetchProductData error: ' . $e->getMessage());
        echo json_encode(array('data' => array(), 'error' => 'Server error'));
    }
}

    /*
    * If the validation is not valid, then it redirects to the create page.
    * If the validation for each input field is valid then it inserts the data into the database 
    * and it stores the operation message into the session flashdata and display on the manage product page
    */
public function create()
{
    if(!in_array('createProduct', $this->permission)) {
        redirect('dashboard', 'refresh');
    }

    $this->form_validation->set_rules('product_name', 'Product name', 'trim|required');
    $this->form_validation->set_rules('price', 'Price', 'trim|required');
    $this->form_validation->set_rules('availability', 'Availability', 'trim|required');
    $this->form_validation->set_rules('imei', 'IMEI', 'trim|required');

    if ($this->form_validation->run() == TRUE) {
        // Generate unique product ID automatically
        $auto_id = 'P'.time().rand(100,999);

        $imei = $this->input->post('imei');
        // Check for duplicate IMEI
        $existing = $this->model_products->getProductByIMEI($imei);
        if ($existing) {
            $this->session->set_flashdata('errors', 'A product with this IMEI already exists. Please use a unique IMEI.');
            redirect('Controller_Products/create', 'refresh');
        }

        $data = array(
            // 'id_no' => $auto_id,             // auto-generated ID
            'name' => $this->input->post('product_name'),
            'imei' => $imei, // manually entered
            'price' => $this->input->post('price'),
            'description' => $this->input->post('description'),
            'storage' => $this->input->post('storage'),
            'warehouse_id' => $this->input->post('warehouse_id'),
            // 'attribute_value_id' => json_encode($this->input->post('attributes_value_id')),
            // 'brand_id' => json_encode($this->input->post('brands')),
            // 'category_id' => json_encode($this->input->post('category')),
            'availability' => $this->input->post('availability'),
            'date_added' => date('Y-m-d'),
        );

        $create = $this->model_products->create($data);
        if($create == true) {
            $this->session->set_flashdata('success', 'Successfully created');
            redirect('Controller_Products/', 'refresh');
        } else {
            $this->session->set_flashdata('errors', 'Error occurred!!');
            redirect('Controller_Products/create', 'refresh');
        }
    }
    else {
        // attributes
        $attribute_data = $this->model_attributes->getActiveAttributeData();
        $attributes_final_data = array();
        foreach ($attribute_data as $k => $v) {
            $attributes_final_data[$k]['attribute_data'] = $v;
            $value = $this->model_attributes->getAttributeValueData($v['id']);
            $attributes_final_data[$k]['attribute_value'] = $value;
        }

        // $this->data['attributes'] = $attributes_final_data;
        // $this->data['brands'] = $this->model_brands->getActiveBrands();         
        // $this->data['category'] = $this->model_category->getActiveCategroy();         

        // Fetch all active warehouses for the dropdown
        $this->data['warehouses'] = $this->model_stores->getActiveStore();
        $this->render_template('products/create', $this->data);
    }   
}

    /*
    * This function is invoked from another function to upload the image into the assets folder
    * and returns the image path
    */
	// public function upload_image()
    // {
    // 	// assets/images/product_image
    //     $config['upload_path'] = 'assets/images/product_image';
    //     $config['file_name'] =  uniqid();
    //     $config['allowed_types'] = 'gif|jpg|png';
    //     $config['max_size'] = '1000';

    //     // $config['max_width']  = '1024';s
    //     // $config['max_height']  = '768';

    //     $this->load->library('upload', $config);
    //     if ( ! $this->upload->do_upload('product_image'))
    //     {
    //         $error = $this->upload->display_errors();
    //         return $error;
    //     }
    //     else
    //     {
    //         $data = array('upload_data' => $this->upload->data());
    //         $type = explode('.', $_FILES['product_image']['name']);
    //         $type = $type[count($type) - 1];
            
    //         $path = $config['upload_path'].'/'.$config['file_name'].'.'.$type;
    //         return ($data == true) ? $path : false;            
    //     }
    // }

    /*
    * If the validation is not valid, then it redirects to the edit product page 
    * If the validation is successfully then it updates the data into the database 
    * and it stores the operation message into the session flashdata and display on the manage product page
    */
	public function update($product_id)
	{      
        if(!in_array('updateProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        if(!$product_id) {
            redirect('dashboard', 'refresh');
        }

        $this->form_validation->set_rules('product_name', 'Product name', 'trim|required');
        // $this->form_validation->set_rules('sku', 'SKU', 'trim|required');
        $this->form_validation->set_rules('price', 'Price', 'trim|required');
        // $this->form_validation->set_rules('store', 'Store', 'trim|required');
        $this->form_validation->set_rules('availability', 'Availability', 'trim|required');

        if ($this->form_validation->run() == TRUE) {
            // true case
            
            $data = array(
                'name' => $this->input->post('product_name'),
                'imei' => $this->input->post('imei'),
                // 'sku' => $this->input->post('sku'),
                'price' => $this->input->post('price'),
                'description' => $this->input->post('description'),
                'storage' => $this->input->post('storage'),
                'warehouse_id' => $this->input->post('warehouse_id'),
                // 'attribute_value_id' => json_encode($this->input->post('attributes_value_id')),
                // 'brand_id' => json_encode($this->input->post('brands')),
                // 'category_id' => json_encode($this->input->post('category')),
                // 'store_id' => $this->input->post('store'),
                'availability' => $this->input->post('availability'),
            );

            
            // if($_FILES['product_image']['size'] > 0) {
            //     $upload_image = $this->upload_image();
            //     $upload_image = array('image' => $upload_image);
                
            //     $this->model_products->update($upload_image, $product_id);
            // }

            $imei = $this->input->post('imei');
            // Check for duplicate IMEI (exclude current product)
            $existing = $this->model_products->getProductByIMEI($imei);
            if ($existing && $existing['id'] != $product_id) {
                $this->session->set_flashdata('errors', 'A product with this IMEI already exists. Please use a unique IMEI.');
                redirect('Controller_Products/update/'.$product_id, 'refresh');
            }

            $update = $this->model_products->update($data, $product_id);
            if($update == true) {
                $this->session->set_flashdata('success', 'Successfully updated');
                redirect('Controller_Products/', 'refresh');
            }
            else {
                $this->session->set_flashdata('errors', 'Error occurred!!');
                redirect('Controller_Products/update/'.$product_id, 'refresh');
            }
        }
        else {
            // attributes 
            $attribute_data = $this->model_attributes->getActiveAttributeData();

            $attributes_final_data = array();
            foreach ($attribute_data as $k => $v) {
                $attributes_final_data[$k]['attribute_data'] = $v;

                $value = $this->model_attributes->getAttributeValueData($v['id']);

                $attributes_final_data[$k]['attribute_value'] = $value;
            }
            
            // false case
            // $this->data['attributes'] = $attributes_final_data;
            // $this->data['brands'] = $this->model_brands->getActiveBrands();         
            // $this->data['category'] = $this->model_category->getActiveCategroy();           
            // $this->data['stores'] = $this->model_stores->getActiveStore();          

            $product_data = $this->model_products->getProductData($product_id);
            $this->data['product_data'] = $product_data;
            // Fetch all active warehouses/branches for the dropdown
            $this->data['warehouses'] = $this->model_stores->getActiveStore();
            $this->render_template('products/edit', $this->data); 
        }   
	}

    /*
    * It removes the data from the database
    * and it returns the response into the json format
    */
	public function remove()
	{
        if(!in_array('deleteProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }
        
        $product_id = $this->input->post('product_id');

        $response = array();
        if($product_id) {
            // Get product info before deleting for notification
            $product = $this->model_products->getProductData($product_id);
            $delete = $this->model_products->remove($product_id);
            if($delete == true) {
                // Notify warehouse member
                if(!empty($product['warehouse_id'])) {
                    $admin_name = $this->session->userdata('username');
                    $msg = 'Product "' . $product['name'] . '" (IMEI: ' . $product['imei'] . ') was removed by ' . $admin_name;
                    $this->model_notifications->notifyWarehouseMembers($product['warehouse_id'], $msg, 'warning');
                }
                $response['success'] = true;
                $response['messages'] = "Successfully removed"; 
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while removing the product information";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refresh the page again!!";
        }

        echo json_encode($response);
	}

	/*
	* Bulk remove products
	*/
	public function bulkRemove()
	{
		if(!in_array('deleteProduct', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$ids = $this->input->post('ids');
		$response = array();

		if(!empty($ids) && is_array($ids)) {
			$success_count = 0;
			$admin_name = $this->session->userdata('username');
			$notified_warehouses = array();

			foreach($ids as $id) {
				$id = (int) $id;
				if($id > 0) {
					$product = $this->model_products->getProductData($id);
					if($this->model_products->remove($id)) {
						$success_count++;
						// Collect warehouse notifications
						if(!empty($product['warehouse_id'])) {
							$wid = $product['warehouse_id'];
							if(!isset($notified_warehouses[$wid])) {
								$notified_warehouses[$wid] = array();
							}
							$notified_warehouses[$wid][] = $product['name'];
						}
					}
				}
			}

			// Send grouped notification per warehouse
			foreach($notified_warehouses as $wid => $names) {
				$count = count($names);
				if($count === 1) {
					$msg = 'Product "' . $names[0] . '" was removed by ' . $admin_name;
				} else {
					$msg = $count . ' products (' . implode(', ', array_slice($names, 0, 3));
					if($count > 3) $msg .= '...';
					$msg .= ') were removed by ' . $admin_name;
				}
				$this->model_notifications->notifyWarehouseMembers($wid, $msg, 'warning');
			}

			$response['success'] = true;
			$response['messages'] = $success_count . ' product(s) successfully removed';
		} else {
			$response['success'] = false;
			$response['messages'] = 'No products selected';
		}

		echo json_encode($response);
	}

}