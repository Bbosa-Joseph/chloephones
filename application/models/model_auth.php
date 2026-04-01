<?php 

class Model_auth extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* 
		This function checks if the email exists in the database
	*/
	public function check_email($email) 
	{
		if($email) {
			$sql = 'SELECT * FROM users WHERE email = ?';
			$query = $this->db->query($sql, array($email));
			$result = $query->num_rows();
			return ($result == 1) ? true : false;
		}

		return false;
	}

	/* 
		This function checks if the email and password matches with the database
	*/
	public function login($email, $password) {
		if($email && $password) {
			$sql = "SELECT * FROM users WHERE email = ?";
			$query = $this->db->query($sql, array($email));

			if($query->num_rows() == 1) {
				$result = $query->row_array();

				$hash_password = password_verify($password, $result['password']);
				if($hash_password === true) {
					return $result;	
				}
				else {
					return false;
				}

				
			}
			else {
				return false;
			}
		}
	}

	/*
	* Records a failed login attempt
	*/
	public function record_login_attempt($ip_address, $email) {
		$this->db->insert('login_attempts', array(
			'ip_address' => $ip_address,
			'email' => $email,
			'attempted_at' => date('Y-m-d H:i:s')
		));
	}

	/*
	* Counts recent failed attempts (last 15 minutes) for an IP
	*/
	public function get_login_attempts($ip_address, $minutes = 15) {
		$this->db->where('ip_address', $ip_address);
		$this->db->where('attempted_at >', date('Y-m-d H:i:s', strtotime("-{$minutes} minutes")));
		return $this->db->count_all_results('login_attempts');
	}

	/*
	* Clears login attempts for an IP after successful login
	*/
	public function clear_login_attempts($ip_address) {
		$this->db->where('ip_address', $ip_address);
		$this->db->delete('login_attempts');
	}
}