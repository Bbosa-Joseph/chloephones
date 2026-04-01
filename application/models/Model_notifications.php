<?php

class Model_notifications extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function create($user_id, $message, $type = 'info')
	{
		return $this->db->insert('notifications', array(
			'user_id'    => (int) $user_id,
			'message'    => $message,
			'type'       => $type,
			'created_at' => date('Y-m-d H:i:s')
		));
	}

	public function notifyWarehouseMembers($warehouse_id, $message, $type = 'info')
	{
		$stores = $this->db->get_where('stores', array('id' => $warehouse_id))->row();
		if ($stores && !empty($stores->assigned_user_id)) {
			$this->create($stores->assigned_user_id, $message, $type);
		}
	}

	public function getUnread($user_id)
	{
		$this->db->where('user_id', (int) $user_id);
		$this->db->where('is_read', 0);
		$this->db->order_by('created_at', 'DESC');
		$this->db->limit(20);
		return $this->db->get('notifications')->result_array();
	}

	public function countUnread($user_id)
	{
		$this->db->where('user_id', (int) $user_id);
		$this->db->where('is_read', 0);
		return $this->db->count_all_results('notifications');
	}

	public function markAsRead($user_id)
	{
		$this->db->where('user_id', (int) $user_id);
		$this->db->where('is_read', 0);
		return $this->db->update('notifications', array('is_read' => 1));
	}

	public function checkNew($user_id, $since)
	{
		$this->db->where('user_id', (int) $user_id);
		$this->db->where('is_read', 0);
		$this->db->where('created_at >', $since);
		$this->db->order_by('created_at', 'DESC');
		return $this->db->get('notifications')->result_array();
	}
}
