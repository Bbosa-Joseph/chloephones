<?php

namespace App\Controllers;

use App\Models\Model_notifications;

class Controller_Notifications extends Admin_Controller
{
    public function __construct()
    {
        $this->not_logged_in();
    }

    public function poll()
    {
        $userId = session()->get('id');
        $since = $this->request->getGet('since');

        $notificationsModel = new Model_notifications();
        $notifications = $since
            ? $notificationsModel->checkNew($userId, $since)
            : $notificationsModel->getUnread($userId);

        $count = $notificationsModel->countUnread($userId);

        return $this->response->setJSON([
            'count' => $count,
            'notifications' => $notifications,
        ]);
    }

    public function markRead()
    {
        $userId = session()->get('id');
        $notificationsModel = new Model_notifications();
        $notificationsModel->markAsRead($userId);

        return $this->response->setJSON(['success' => true]);
    }
}
