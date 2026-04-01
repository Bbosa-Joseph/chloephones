<?php 

class Model_products extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function countTotalPhones()
{
	$sql = "SELECT * FROM phones";
	$query = $this->db->query($sql);
	return $query->num_rows();
}

public function countAvailablePhones()
{
	$sql = "SELECT * FROM phones WHERE status='available'";
	$query = $this->db->query($sql);
	return $query->num_rows();
}

public function countSoldPhones()
{
	$sql = "SELECT * FROM phones WHERE status='sold'";
	$query = $this->db->query($sql);
	return $query->num_rows();
}

}