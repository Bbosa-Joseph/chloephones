<?php 

class Model_brands extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get active brands */
	public function getActiveBrands()
	{
		$sql = "SELECT * FROM brands WHERE active = ? ORDER BY name ASC";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	/* get brand data */
	public function getBrandData($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM brands WHERE id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM brands ORDER BY id DESC";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	/* create brand */
	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('brands', $data);
			return ($insert == true) ? true : false;
		}
	}

	/* update brand */
	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('id', $id);
			$update = $this->db->update('brands', $data);
			return ($update == true) ? true : false;
		}
	}

	/* delete brand */
	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('brands');
			return ($delete == true) ? true : false;
		}
	}

	/* count total brands (dashboard) */
	public function countTotalBrands()
	{
		$sql = "SELECT * FROM brands";
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

	/* search brand by name */
	public function searchBrand($name)
	{
		$sql = "SELECT * FROM brands WHERE name LIKE ?";
		$query = $this->db->query($sql, array('%'.$name.'%'));
		return $query->result_array();
	}

}