<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Controller_Orders extends Admin_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Orders';

		$this->load->model('model_orders');
		$this->load->model('model_products');
		$this->load->model('model_company');
	}

	/* 
	* It only redirects to the manage order page
	*/
	public function index()
	{
		if(!in_array('viewOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->data['page_title'] = 'Manage Orders';
		$this->render_template('orders/index', $this->data);		
	}

	/*
	* Fetches the orders data from the orders table 
	* this function is called from the datatable ajax function
	*/
	public function fetchOrdersData()
	{
		$result = array('data' => array());

		$data = $this->model_orders->getOrdersData();

		foreach ($data as $key => $value) {

			$count_total_item = $this->model_orders->countOrderItem($value['id']);
			$date = date('d-m-Y', $value['date_time']);
			$time = date('h:i a', $value['date_time']);

			$date_time = $date . ' ' . $time;

			// checkbox
			$checkbox = '<input type="checkbox" class="order-checkbox" value="'.$value['id'].'">';

			// button
			$buttons = '';

			if(in_array('viewOrder', $this->permission)) {
				$buttons .= '<a target="__blank" href="'.base_url('Controller_Orders/printDiv/'.$value['id']).'" class="btn btn-primary btn-sm" title="Print"><i class="fa fa-print"></i></a>';
			}

			if(in_array('updateOrder', $this->permission)) {
				$buttons .= ' <a href="'.base_url('Controller_Orders/update/'.$value['id']).'" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>';
			}

			if(in_array('deleteOrder', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-danger btn-sm" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
			}

			if($value['paid_status'] == 1) {
				$paid_status = '<span class="label label-success">Paid</span>';	
			}
			else {
				$paid_status = '<span class="label label-warning">Not Paid</span>';
			}

			$row = array();
			if(in_array('deleteOrder', $this->permission)) {
				$row[] = $checkbox;
			}
			$row[] = $value['bill_no'];
			$row[] = $value['customer_name'];
			$row[] = $value['customer_phone'];
			$row[] = 'UGX'.$value['net_amount'];
			$row[] = $buttons;

			$result['data'][$key] = $row;
		} // /foreach

		echo json_encode($result);
	}

	/*
	* If the validation is not valid, then it redirects to the create page.
	* If the validation for each input field is valid then it inserts the data into the database 
	* and it stores the operation message into the session flashdata and display on the manage group page
	*/
	public function create()
	{
		if(!in_array('createOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->data['page_title'] = 'Add Order';

		$this->form_validation->set_rules('product[]', 'Product name', 'trim|required');
		
	
        if ($this->form_validation->run() == TRUE) {        	
			$duplicate_imeis = $this->_getDuplicateOrderImeis($this->input->post('product'));
			if (!empty($duplicate_imeis)) {
				$this->session->set_flashdata('errors', 'Duplicate IMEI in this order: ' . implode(', ', $duplicate_imeis));
				redirect('Controller_Orders/create/', 'refresh');
			}
        	
        	$order_id = $this->model_orders->create();
        	
        	if($order_id) {
        		$this->session->set_flashdata('success', 'Successfully created');
				redirect('Controller_Orders/printDiv/'.$order_id.'?from=create', 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('Controller_Orders/create/', 'refresh');
        	}
        }
		else {
			// false case
			$company = $this->model_company->getCompanyData(1);
			$this->data['company_data'] = $company;
			$this->data['is_vat_enabled'] = ($company['vat_charge_value'] > 0) ? true : false;
			$this->data['is_service_enabled'] = ($company['service_charge_value'] > 0) ? true : false;

			// If IMEI is passed in the query, pre-select that product
			$imei = $this->input->get('imei', true);
			if ($imei) {
				$product = $this->model_products->getProductByIMEI($imei);
				$this->data['products'] = $product ? array($product) : array();
			} else {
				$this->data['products'] = $this->model_products->getActiveProductData();
			}

			$this->render_template('orders/create', $this->data);
		}  
	}

	/*
	* It gets the product id passed from the ajax method.
	* It checks retrieves the particular product data from the product id 
	* and return the data into the json format.
	*/
	public function getProductValueById()
	{
		$product_id = $this->input->post('product_id');
		if($product_id) {
			$product_data = $this->model_products->getProductData($product_id);
			echo json_encode($product_data);
		}
	}

	/*
	* It gets the all the active product inforamtion from the product table 
	* This function is used in the order page, for the product selection in the table
	* The response is return on the json format.
	*/
	public function getTableProductRow()
	{
		$products = $this->model_products->getActiveProductData();
		echo json_encode($products);
	}

	/*
	* If the validation is not valid, then it redirects to the edit orders page 
	* If the validation is successfully then it updates the data into the database 
	* and it stores the operation message into the session flashdata and display on the manage group page
	*/
	public function update($id)
	{
		if(!in_array('updateOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		if(!$id) {
			redirect('dashboard', 'refresh');
		}

		$this->data['page_title'] = 'Update Order';

		$this->form_validation->set_rules('product[]', 'Product name', 'trim|required');
		
	
        if ($this->form_validation->run() == TRUE) {        	
			$duplicate_imeis = $this->_getDuplicateOrderImeis($this->input->post('product'));
			if (!empty($duplicate_imeis)) {
				$this->session->set_flashdata('errors', 'Duplicate IMEI in this order: ' . implode(', ', $duplicate_imeis));
				redirect('Controller_Orders/update/'.$id, 'refresh');
			}
        	
        	$update = $this->model_orders->update($id);
        	
        	if($update == true) {
        		$this->session->set_flashdata('success', 'Successfully updated');
        		redirect('Controller_Orders/update/'.$id, 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('Controller_Orders/update/'.$id, 'refresh');
        	}
        }
        else {
            // false case
        	$company = $this->model_company->getCompanyData(1);
        	$this->data['company_data'] = $company;
        	$this->data['is_vat_enabled'] = ($company['vat_charge_value'] > 0) ? true : false;
        	$this->data['is_service_enabled'] = ($company['service_charge_value'] > 0) ? true : false;

        	$result = array();

			$orders_data = $this->model_orders->getOrdersData($id);
			$result['order'] = $orders_data;
			$orders_item = array();
			if (isset($orders_data['id'])) {
				$orders_item = $this->model_orders->getOrdersItemData($orders_data['id']);
			}
			foreach($orders_item as $k => $v) {
				$result['order_item'][] = $v;
			}

    		$this->data['order_data'] = $result;

        	$this->data['products'] = $this->model_products->getActiveProductData();      	

            $this->render_template('orders/edit', $this->data);
        }
	}

	/*
	* It removes the data from the database
	* and it returns the response into the json format
	*/
	public function remove()
	{
		if(!in_array('deleteOrder', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$order_id = $this->input->post('order_id');

        $response = array();
        if($order_id) {
            $delete = $this->model_orders->remove($order_id);
            if($delete == true) {
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
            $response['messages'] = "Refersh the page again!!";
        }

        echo json_encode($response); 
	}

	/*
	* Bulk remove orders
	*/
	public function bulkRemove()
	{
		if(!in_array('deleteOrder', $this->permission)) {
			echo json_encode(array('success' => false, 'messages' => 'Permission denied'));
			return;
		}

		$order_ids = $this->input->post('order_ids');
		$response = array();

		if(!empty($order_ids) && is_array($order_ids)) {
			$deleted = 0;
			foreach($order_ids as $id) {
				$id = (int) $id;
				if($id > 0 && $this->model_orders->remove($id)) {
					$deleted++;
				}
			}
			$response['success'] = true;
			$response['messages'] = $deleted . ' order(s) deleted successfully';
		} else {
			$response['success'] = false;
			$response['messages'] = 'No orders selected';
		}

		echo json_encode($response);
	}


	// IME selection 

	public function getProductByIMEI()
{
    $imei = $this->input->post('imei');
    if($imei) {
        $this->load->model('model_products');
        $product = $this->model_products->getProductByIMEI($imei);
        if($product) {
            echo json_encode(['status' => 'success', 'data' => $product]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'IMEI not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'IMEI missing']);
    }
}

private function _getDuplicateOrderImeis($product_ids)
{
	if (!is_array($product_ids) || empty($product_ids)) {
		return array();
	}

	$normalized_ids = array();
	foreach ($product_ids as $product_id) {
		$product_id = (int) $product_id;
		if ($product_id > 0) {
			$normalized_ids[] = $product_id;
		}
	}

	if (empty($normalized_ids)) {
		return array();
	}

	$id_counts = array_count_values($normalized_ids);
	$duplicate_imeis = array();

	foreach ($id_counts as $product_id => $count) {
		if ($count > 1) {
			$product = $this->model_products->getProductData($product_id);
			if (!empty($product['imei'])) {
				$duplicate_imeis[] = $product['imei'];
			} else {
				$duplicate_imeis[] = 'Product ID ' . $product_id;
			}
		}
	}

	return $duplicate_imeis;
}

	/*
	* It gets the product id and fetch the order data. 
	* The order print logic is done here 
	*/
	public function printDiv($id)
{
		if(!in_array('viewOrder', $this->permission) && !in_array('createOrder', $this->permission)) {
        redirect('dashboard', 'refresh');
    }
    
    if($id) {
        $order_data = $this->model_orders->getOrdersData($id);
        $orders_items = $this->model_orders->getOrdersItemData($id);
        $company_info = $this->model_company->getCompanyData(1);

        $order_date = date('d/m/Y H:i', $order_data['date_time']);
        $paid_status = ($order_data['paid_status'] == 1) ? "Paid" : "Unpaid";
        $served_by = $this->session->userdata('username');
		$from_create = $this->input->get('from', true);
		$return_url = ($from_create === 'create') ? base_url('Controller_Orders/create?printed=1') : '';

		$html = $this->_buildReceiptHtml($order_data, $orders_items, $company_info, $order_date, $served_by, true, $return_url);
        echo $html;
    }
}

/*
* Download receipt as PDF
*/
public function downloadPDF($id)
{
    if(!in_array('viewOrder', $this->permission)) {
        redirect('dashboard', 'refresh');
    }

    if($id) {
        require_once FCPATH . 'vendor/autoload.php';

        $order_data = $this->model_orders->getOrdersData($id);
        $orders_items = $this->model_orders->getOrdersItemData($id);
        $company_info = $this->model_company->getCompanyData(1);

        $order_date = date('d/m/Y H:i', $order_data['date_time']);
        $served_by = $this->session->userdata('username');

		$html = $this->_buildReceiptHtml($order_data, $orders_items, $company_info, $order_date, $served_by, false, '');

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'sans-serif');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();

        $filename = 'Receipt_' . $order_data['bill_no'] . '.pdf';
        $dompdf->stream($filename, array('Attachment' => true));
    }
}

/*
* Build receipt HTML (shared between print and PDF)
*/
private function _buildReceiptHtml($order_data, $orders_items, $company_info, $order_date, $served_by, $auto_print = true, $return_url = '')
{
		$logo_url = base_url('assets/images/product_image/chloe2.png');
		$return_js = '';
		if ($auto_print && !empty($return_url)) {
			$return_js = '<script>window.onafterprint = function(){ window.location.href = "'. $return_url .'"; };</script>';
		}

        $html = '<!DOCTYPE html>
	<html>
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Receipt</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<style>
	body { font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif; color: #1f2937; background: #e5e7eb; }
	.invoice { max-width: 520px; margin: 24px auto; padding: 24px 22px; border-radius: 12px; border: 1px solid #cbd5e1; background: #ffffff; box-shadow: 0 8px 26px rgba(15,23,42,0.10); }
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
	.receipt-actions { text-align: center; margin: 20px auto; max-width: 520px; }
	.receipt-actions .btn { display: inline-block; padding: 10px 24px; margin: 0 6px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; color: #fff; }
	.receipt-actions .btn-print { background: #0f4c81; }
	.receipt-actions .btn-pdf { background: #059669; }
	.receipt-actions .btn:hover { opacity: 0.9; }
	</style>
	</head>
	<body'.($auto_print ? ' onload="window.print();"' : '').'>

	'.($auto_print ? '' : '<div class="receipt-actions">
		<button class="btn btn-print" onclick="window.print();">Print Receipt</button>
		<a class="btn btn-pdf" href="'.base_url('Controller_Orders/downloadPDF/'.$order_data['id']).'">Download PDF</a>
	</div>').'

	<div class="invoice">
		<div class="invoice-header">
			<div class="brand">
				<img src="'. $logo_url .'" alt="Company Logo" class="brand-logo">
				<div class="brand-text">
					<h1>Chlo Phone Center</h1>
					<p>Sales Receipt</p>
				</div>
			</div>
			<div class="receipt-date">Date: '. $order_date .'</div>
		</div>

		<div class="invoice-meta">
			<div><span>Receipt No:</span> <span>'. $order_data['bill_no'] .'</span></div>
			<div class="section-title">Customer Details</div>
			<div><span>Customer:</span> <span>'. $order_data['customer_name'] .'</span></div>
			<div><span>Contact:</span> <span>'. $order_data['customer_phone'] .'</span></div>
			<div><span>NIN No:</span> <span>'. $order_data['customer_address'] .'</span></div>
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

				   foreach ($orders_items as $item) {
					   $product = $this->model_products->getProductData($item['product_id']);
					   $ram_line = '';
					   $storage_line = '';
					   if (isset($product['ram']) && $product['ram'] !== '') {
						   $ram_line = '<small>RAM: ' . htmlspecialchars($product['ram']) . ' GB</small><br>';
					   }
					   if (isset($product['storage']) && $product['storage'] !== '') {
						   $storage_line = '<small>Storage: ' . htmlspecialchars($product['storage']) . ' GB</small><br>';
					   }
					   $imei = $product['imei'];

					   $html .= '<tr>
						   <td>
							   <strong>'. htmlspecialchars($product['name']) .'</strong><br>
							   '. $ram_line . $storage_line .'
							   <span class="imei-code">IMEI: '. htmlspecialchars($imei) .'</span>
						   </td>
						   <td class="text-right">01</td>
						   <td class="text-right">'. number_format($item['amount'], 0) .' UGX</td>
					   </tr>';
				   }

			$html .= '</tbody>
			</table>
		</div>

		<div class="invoice-totals">
			<div><span>Total:</span> <span>'. number_format($order_data['net_amount'], 0) .' UGX</span></div>
			   <!-- Payment Method and Bill Status removed as per new requirements -->
		</div>

		<div class="invoice-footer">
			<p>Thank you for your business!</p>
			'. (!empty($served_by) ? '<div class="served-by">Served by: '. htmlspecialchars($served_by) .'</div>' : '') .'
		</div>
	</div>

	'. $return_js .'
	</body>
</html>';

        return $html;
}
}