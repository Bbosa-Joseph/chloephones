<?php 

class Model_orders extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get the orders data */
	public function getOrdersData($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM orders WHERE id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM orders ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	/* get the orders item data */
	public function getOrdersItemData($order_id = null)
	{
		if(!$order_id) {
			return false;
		}

		$sql = "SELECT * FROM orders_item WHERE order_id = ?";
		$query = $this->db->query($sql, array($order_id));
		return $query->result_array();
	}

	/* create order */
	public function create()
	{
		$user_id = $this->session->userdata('id');

		// receipt number
		$bill_no = 'N0:'.date('YmdHis');

    	$data = array(
    		'bill_no' => $bill_no,
    		'customer_name' => $this->input->post('customer_name'),
    		'customer_address' => $this->input->post('customer_address'),
    		'customer_phone' => $this->input->post('customer_phone'),
    		'date_time' => strtotime(date('Y-m-d h:i:s a')),
    		'gross_amount' => $this->input->post('gross_amount_value'),
    		'service_charge_rate' => $this->input->post('service_charge_rate'),
    		'service_charge' => ($this->input->post('service_charge_value') > 0) ? $this->input->post('service_charge_value'):0,
    		'vat_charge_rate' => $this->input->post('vat_charge_rate'),
    		'vat_charge' => ($this->input->post('vat_charge_value') > 0) ? $this->input->post('vat_charge_value') : 0,
    		'net_amount' => $this->input->post('net_amount_value'),
    		'discount' => $this->input->post('discount'),
    		'paid_status' => 2,
    		'user_id' => $user_id
    	);

		$insert = $this->db->insert('orders', $data);
		$order_id = $this->db->insert_id();

		$this->load->model('model_products');

		$count_product = count($this->input->post('product'));

    	for($x = 0; $x < $count_product; $x++) {

			$items = array(
				'order_id' => $order_id,
				'product_id' => $this->input->post('product')[$x],
				'rate' => $this->input->post('rate_value')[$x],
				'amount' => $this->input->post('amount_value')[$x],
			);

    		$this->db->insert('orders_item', $items);

    		/* decrease stock */
    		$product_data = $this->model_products->getProductData($this->input->post('product')[$x]);



    		/*
    		FOR PHONE SYSTEM (OPTIONAL LATER)
    		If every phone has IMEI and single stock:

    		$update_product = array('status' => 'sold');
    		$this->model_products->update($update_product, $this->input->post('product')[$x]);
    		*/
    	}

		return ($order_id) ? $order_id : false;
	}

	/* count order items */
	public function countOrderItem($order_id)
	{
		if($order_id) {
			$sql = "SELECT * FROM orders_item WHERE order_id = ?";
			$query = $this->db->query($sql, array($order_id));
			return $query->num_rows();
		}
	}

	/* update order */
	public function update($id)
	{
		if($id) {

			$user_id = $this->session->userdata('id');

			$data = array(
				'customer_name' => $this->input->post('customer_name'),
	    		'customer_address' => $this->input->post('customer_address'),
	    		'customer_phone' => $this->input->post('customer_phone'),
	    		'gross_amount' => $this->input->post('gross_amount_value'),
	    		'service_charge_rate' => $this->input->post('service_charge_rate'),
	    		'service_charge' => ($this->input->post('service_charge_value') > 0) ? $this->input->post('service_charge_value'):0,
	    		'vat_charge_rate' => $this->input->post('vat_charge_rate'),
	    		'vat_charge' => ($this->input->post('vat_charge_value') > 0) ? $this->input->post('vat_charge_value') : 0,
	    		'net_amount' => $this->input->post('net_amount_value'),
	    		'discount' => $this->input->post('discount'),
	    		'paid_status' => $this->input->post('paid_status'),
	    		'user_id' => $user_id
	    	);

			$this->db->where('id', $id);
			$this->db->update('orders', $data);

			$this->load->model('model_products');

			$get_order_item = $this->getOrdersItemData($id);



			$this->db->where('order_id', $id);
			$this->db->delete('orders_item');

			$count_product = count($this->input->post('product'));
			for($x = 0; $x < $count_product; $x++) {
				$items = array(
					'order_id' => $id,
					'product_id' => $this->input->post('product')[$x],
					'rate' => $this->input->post('rate_value')[$x],
					'amount' => $this->input->post('amount_value')[$x],
				);
				$this->db->insert('orders_item', $items);
			}

			return true;
		}
	}

	/* remove order */
	public function remove($id)
	{
		if($id) {

			$this->db->where('id', $id);
			$delete = $this->db->delete('orders');

			$this->db->where('order_id', $id);
			$delete_item = $this->db->delete('orders_item');

			return ($delete == true && $delete_item) ? true : false;
		}
	}

	/* total paid orders (dashboard) */
	public function countTotalPaidOrders()
	{
		$sql = "SELECT * FROM orders WHERE paid_status = ?";
		$query = $this->db->query($sql, array(1));
		return $query->num_rows();
	}

	   /* total unpaid orders (dashboard) */
    public function countTotalUnpaidOrders()
    {
        $sql = "SELECT * FROM orders WHERE paid_status = ?";
        $query = $this->db->query($sql, array(2)); // 2 = unpaid
        return $query->num_rows();
    }

	/* SALES TODAY (for dashboard) */
	public function countTodaySales()
	{
		$sql = "SELECT * FROM orders WHERE DATE(FROM_UNIXTIME(date_time)) = CURDATE()";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* TOTAL SALES AMOUNT */
	public function totalSalesAmount()
	{
		$sql = "SELECT SUM(net_amount) as total FROM orders";
		$query = $this->db->query($sql);
		return $query->row()->total;
	}

}