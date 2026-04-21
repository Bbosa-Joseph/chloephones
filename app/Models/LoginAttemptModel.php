<?php

namespace App\Models;

class LoginAttemptModel extends BaseModel
{
    protected $table      = 'login_attempts';
    protected $primaryKey = 'id';

    protected $useTimestamps = false;
    protected $allowedFields = ['ip_address', 'email'];

    public function countRecent(string $ip, string $email, int $minutes = 15): int
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        return $this->where('ip_address', $ip)
                    ->where('email', $email)
                    ->where('attempted_at >=', $since)
                    ->countAllResults();
    }

    public function purgeOld(int $hours = 24): void
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        $this->where('attempted_at <', $cutoff)->delete();
    }
}
