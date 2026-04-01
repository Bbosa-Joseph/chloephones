<?php 

class Model_products extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get product / phone data */
	public function getProductData($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM products WHERE id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM products ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	// ProductByIMEI
	public function getProductByIMEI($imei)
	{
		$sql = "SELECT * FROM products WHERE imei = ? LIMIT 1";
		$query = $this->db->query($sql, [$imei]);
		return $query->row_array();
	}

	/* get available phones (not sold) */
	public function getActiveProductData()
	{
		$sql = "SELECT * FROM products WHERE availability = ? ORDER BY id DESC";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	/* create phone */
	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('products', $data);
			return ($insert == true) ? true : false;
		}
	}

	/* update phone */
	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('id', $id);
			$update = $this->db->update('products', $data);
			return ($update == true) ? true : false;
		}
	}

	/* delete phone */
	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('products');
			return ($delete == true) ? true : false;
		}
	}

	/* count all phones */
	public function countTotalProducts()
	{
		$sql = "SELECT * FROM products";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* count available phones */
	public function countAvailablePhones()
	{
		$sql = "SELECT * FROM products WHERE availability = 1";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* count sold phones */
	public function countSoldPhones()
	{
		$sql = "SELECT * FROM products WHERE availability = 0";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* search phone by IMEI */
	public function searchByIMEI($imei)
	{
		$sql = "SELECT * FROM products WHERE imei = ?";
		$query = $this->db->query($sql, array($imei));
		return $query->row_array();
	}

	/* mark phone as sold */
	public function markPhoneSold($id)
	{
		$data = array(
			'availability' => 0
		);

		$this->db->where('id', $id);
		return $this->db->update('products', $data);
	}

	/* count brands */
	public function countTotalbrands()
	{
		$sql = "SELECT * FROM brands";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* count categories */
	public function countTotalcategory()
	{
		$sql = "SELECT * FROM categories";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* count attributes */
	public function countTotalattribures()
	{
		$sql = "SELECT * FROM attributes";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* count out-of-stock (inactive) products */
	public function countOutOfStock()
	{
		$sql = "SELECT COUNT(*) as cnt FROM products WHERE availability = 0";
		return $this->db->query($sql)->row()->cnt;
	}

	/* count aged products (added 15+ days ago) */
	public function countAgedProducts()
	{
		$sql = "SELECT COUNT(*) as cnt FROM products WHERE date_added <= DATE_SUB(NOW(), INTERVAL 15 DAY) AND availability = 1";
		return $this->db->query($sql)->row()->cnt;
	}

	/* total stock value */
	public function getTotalStockValue()
	{
		$sql = "SELECT COALESCE(SUM(price), 0) as total FROM products WHERE availability = 1";
		return $this->db->query($sql)->row()->total;
	}

}