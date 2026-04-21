<?php

namespace App\Models;

class NotificationModel extends BaseModel
{
    protected $table      = 'notifications';
    protected $primaryKey = 'id';

    // only created_at, no updated_at
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id', 'title', 'message', 'type', 'link', 'is_read',
    ];

    /** Unread count for a user (cheap query for nav badge). */
    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)->where('is_read', 0)->countAllResults();
    }

    /** Mark all of a user's notifications as read. */
    public function markAllRead(int $userId): void
    {
        $this->where('user_id', $userId)->set('is_read', 1)->update();
    }

    /** Send a notification to a user. */
    public function notify(
        int    $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $link = null
    ): int {
        return $this->insert(compact('user_id', 'title', 'message', 'type', 'link'));
    }
}
