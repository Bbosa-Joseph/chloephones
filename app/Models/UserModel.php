<?php

namespace App\Models;

class UserModel extends BaseModel
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'username', 'email', 'password',
        'first_name', 'lastname', 'phone', 'gender', 'avatar',
        'is_active', 'last_login_at',
        'reset_hash', 'reset_expires_at', 'remember_token',
    ];

    // Never return password hash in normal queries — use withPassword() when needed
    protected $hiddenFields = ['password', 'reset_hash', 'remember_token'];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected $validationRules = [
        'username'   => 'required|min_length[3]|max_length[60]|is_unique[users.username,id,{id}]',
        'email'      => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password'   => 'required|min_length[8]',
        'first_name' => 'required|max_length[80]',
        'last_name'  => 'required|max_length[80]',
    ];

    // ── Callbacks ──────────────────────────────────────────────────────────────

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }
        return $data;
    }

    // ── Custom Finders ─────────────────────────────────────────────────────────

    /** Find user plus all their role names. */
    public function findWithRoles(int $userId): ?array
    {
        $user = $this->find($userId);
        if (! $user) {
            return null;
        }
        $user['roles'] = $this->db->table('user_roles ur')
            ->select('r.id, r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()->getResultArray();
        return $user;
    }

    /** Collect the flat list of permission names for a user (used by AuthFilter). */
    public function getPermissions(int $userId): array
    {
        $db = $this->db;

        // Prefer legacy permissions if present (these are edited in the UI).
        if ($db->tableExists('user_group') && $db->tableExists('user_groups')) {
            $row = $db->table('user_group ug')
                ->select('g.permission')
                ->join('user_groups g', 'g.id = ug.group_id', 'left')
                ->where('ug.user_id', $userId)
                ->get()
                ->getRowArray();

            if (!empty($row['permission'])) {
                $decoded = @unserialize($row['permission']);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        if ($db->tableExists('user_roles') && $db->tableExists('role_permissions') && $db->tableExists('permissions')) {
            $rows = $db->table('user_roles ur')
                ->select('p.name')
                ->join('role_permissions rp', 'rp.role_id = ur.role_id')
                ->join('permissions p',       'p.id = rp.permission_id')
                ->where('ur.user_id', $userId)
                ->get()
                ->getResultArray();

            if (!empty($rows)) {
                return array_column($rows, 'name');
            }
        }

        return [];
    }

    /** Locate user by remember-me token. */
    public function findByRememberToken(string $token): ?array
    {
        return $this->where('remember_token', $token)->first();
    }

    /** Locate user by password-reset hash (and check expiry). */
    public function findByResetHash(string $hash): ?array
    {
        return $this->where('reset_hash', $hash)
                    ->where('reset_expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }

    /** Stamp last login time. */
    public function touchLogin(int $userId): void
    {
        $this->set('last_login_at', date('Y-m-d H:i:s'))->update($userId);
    }

    /**
     * Fetch a row that includes the hashed password — for authentication only.
     * Uses a raw DB query to intentionally bypass $hiddenFields.
     * Never expose the returned array to end-users or views.
     */
    public function findForAuth(string $email): ?array
    {
        $row = $this->db->table($this->table)
            ->select(['id', 'username', 'email', 'password',
                      'firstname', 'lastname', 'is_active', 'last_login_at'])
            ->where('email', $email)
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }
}
