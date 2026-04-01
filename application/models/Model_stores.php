<?php 

class Model_stores extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get the active store data */
	public function getActiveStore()
	{
		$sql = "SELECT * FROM stores WHERE active = ?";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	/* get the brand data */
	public function getStoresData($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM stores where id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT s.*, 
				COUNT(p.id) AS total_stock, 
				COALESCE(SUM(p.price), 0) AS total_value 
				FROM stores s 
				LEFT JOIN products p ON p.warehouse_id = s.id 
				GROUP BY s.id";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('stores', $data);
			return ($insert == true) ? true : false;
		}
	}

	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('id', $id);
			$update = $this->db->update('stores', $data);
			return ($update == true) ? true : false;
		}
	}

	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('stores');
			return ($delete == true) ? true : false;
		}
	}

	public function countTotalStores()
	{
		$sql = "SELECT * FROM stores WHERE active = ?";
		$query = $this->db->query($sql, array(1));
		return $query->num_rows();
	}

	/**
	 * Check if a user is already assigned to a warehouse.
	 * Returns the warehouse row if found, or null.
	 * Optionally exclude a warehouse id (for updates).
	 */
	public function getStoreByUserId($user_id, $exclude_store_id = null)
	{
		if(empty($user_id)) return null;
		$sql = "SELECT * FROM stores WHERE assigned_user_id = ?";
		$params = array($user_id);
		if($exclude_store_id) {
			$sql .= " AND id != ?";
			$params[] = $exclude_store_id;
		}
		$query = $this->db->query($sql, $params);
		return $query->row_array();
	}

}