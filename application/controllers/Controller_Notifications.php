<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Controller_Notifications extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->not_logged_in();
		$this->load->model('model_notifications');
	}

	/*
	* Poll for unread notifications (called by AJAX)
	*/
	public function poll()
	{
		$user_id = $this->session->userdata('id');
		$since = $this->input->get('since');

		if ($since) {
			$notifications = $this->model_notifications->checkNew($user_id, $since);
		} else {
			$notifications = $this->model_notifications->getUnread($user_id);
		}

		$count = $this->model_notifications->countUnread($user_id);

		echo json_encode(array(
			'count' => $count,
			'notifications' => $notifications
		));
	}

	/*
	* Mark all notifications as read
	*/
	public function markRead()
	{
		$user_id = $this->session->userdata('id');
		$this->model_notifications->markAsRead($user_id);
		echo json_encode(array('success' => true));
	}
}
