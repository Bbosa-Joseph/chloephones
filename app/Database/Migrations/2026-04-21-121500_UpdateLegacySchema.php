<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateLegacySchema extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // ---------------------------------------------------------------------
        // Users: add new auth fields (first_name/last_name/remember/reset)
        // ---------------------------------------------------------------------
        if ($db->tableExists('users')) {
            if (! $db->fieldExists('first_name', 'users')) {
                $this->forge->addColumn('users', [
                    'first_name' => [
                        'type' => 'VARCHAR',
                        'constraint' => 120,
                        'null' => true,
                        'after' => 'email',
                    ],
                ]);
            }
            if (! $db->fieldExists('last_name', 'users')) {
                $this->forge->addColumn('users', [
                    'last_name' => [
                        'type' => 'VARCHAR',
                        'constraint' => 120,
                        'null' => true,
                        'after' => 'first_name',
                    ],
                ]);
            }
            if (! $db->fieldExists('remember_token', 'users')) {
                $this->forge->addColumn('users', [
                    'remember_token' => [
                        'type' => 'VARCHAR',
                        'constraint' => 128,
                        'null' => true,
                        'after' => 'last_login_at',
                    ],
                ]);
            }
            if (! $db->fieldExists('reset_hash', 'users')) {
                $this->forge->addColumn('users', [
                    'reset_hash' => [
                        'type' => 'VARCHAR',
                        'constraint' => 64,
                        'null' => true,
                        'after' => 'remember_token',
                    ],
                ]);
            }
            if (! $db->fieldExists('reset_expires_at', 'users')) {
                $this->forge->addColumn('users', [
                    'reset_expires_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                        'after' => 'reset_hash',
                    ],
                ]);
            }
            if (! $db->fieldExists('avatar', 'users')) {
                $this->forge->addColumn('users', [
                    'avatar' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'null' => true,
                        'after' => 'gender',
                    ],
                ]);
            }

            // Backfill first_name/last_name from legacy columns if present
            if ($db->fieldExists('firstname', 'users') && $db->fieldExists('first_name', 'users')) {
                $db->query("UPDATE users SET first_name = firstname WHERE first_name IS NULL AND firstname IS NOT NULL");
            }
            if ($db->fieldExists('lastname', 'users') && $db->fieldExists('last_name', 'users')) {
                $db->query("UPDATE users SET last_name = lastname WHERE last_name IS NULL AND lastname IS NOT NULL");
            }
        }

        // ---------------------------------------------------------------------
        // login_attempts: default attempted_at
        // ---------------------------------------------------------------------
        if ($db->tableExists('login_attempts') && $db->fieldExists('attempted_at', 'login_attempts')) {
            $db->query("ALTER TABLE login_attempts MODIFY attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
        }

        // ---------------------------------------------------------------------
        // notifications: add title/link columns
        // ---------------------------------------------------------------------
        if ($db->tableExists('notifications')) {
            if (! $db->fieldExists('title', 'notifications')) {
                $this->forge->addColumn('notifications', [
                    'title' => [
                        'type' => 'VARCHAR',
                        'constraint' => 190,
                        'null' => true,
                        'after' => 'user_id',
                    ],
                ]);
            }
            if (! $db->fieldExists('link', 'notifications')) {
                $this->forge->addColumn('notifications', [
                    'link' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'null' => true,
                        'after' => 'type',
                    ],
                ]);
            }
        }

        // ---------------------------------------------------------------------
        // company_settings table (single-row settings)
        // ---------------------------------------------------------------------
        if (! $db->tableExists('company_settings')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'company_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 190,
                    'null' => true,
                ],
                'address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'phone' => [
                    'type' => 'VARCHAR',
                    'constraint' => 60,
                    'null' => true,
                ],
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 190,
                    'null' => true,
                ],
                'website' => [
                    'type' => 'VARCHAR',
                    'constraint' => 190,
                    'null' => true,
                ],
                'country' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'currency' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => true,
                ],
                'currency_symbol' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => true,
                ],
                'tax_rate' => [
                    'type' => 'DECIMAL',
                    'constraint' => '6,2',
                    'default' => 0.00,
                ],
                'service_charge_rate' => [
                    'type' => 'DECIMAL',
                    'constraint' => '6,2',
                    'default' => 0.00,
                ],
                'logo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'footer_message' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('company_settings');

            // Seed from legacy company table if present
            if ($db->tableExists('company')) {
                $row = $db->table('company')->get()->getRowArray();
                if ($row) {
                    $db->table('company_settings')->insert([
                        'id' => 1,
                        'company_name' => $row['company_name'] ?? null,
                        'currency' => $row['currency'] ?? null,
                        'tax_rate' => (float) ($row['vat_charge_value'] ?? 0),
                        'service_charge_rate' => (float) ($row['service_charge_value'] ?? 0),
                    ]);
                }
            }
        }

        // ---------------------------------------------------------------------
        // RBAC tables for modern Auth (roles/permissions)
        // ---------------------------------------------------------------------
        if (! $db->tableExists('permissions')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'module' => [
                    'type' => 'VARCHAR',
                    'constraint' => 60,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('permissions');
        }

        if (! $db->tableExists('roles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('roles');
        }

        if (! $db->tableExists('user_roles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'role_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['user_id', 'role_id']);
            $this->forge->createTable('user_roles');
        }

        if (! $db->tableExists('role_permissions')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'role_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'permission_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['role_id', 'permission_id']);
            $this->forge->createTable('role_permissions');
        }

        // ---------------------------------------------------------------------
        // Migrate legacy user_groups data into roles/permissions
        // ---------------------------------------------------------------------
        if ($db->tableExists('user_groups')) {
            $groups = $db->table('user_groups')->get()->getResultArray();
            $permissionLookup = [];
            $roleLookup = [];

            foreach ($groups as $group) {
                $roleName = $group['group_name'] ?? null;
                if (! $roleName) {
                    continue;
                }

                // Create role if missing
                $existingRole = $db->table('roles')->where('name', $roleName)->get()->getRowArray();
                if ($existingRole) {
                    $roleId = (int) $existingRole['id'];
                } else {
                    $db->table('roles')->insert([
                        'name' => $roleName,
                        'description' => null,
                        'is_active' => 1,
                    ]);
                    $roleId = (int) $db->insertID();
                }
                $roleLookup[(int) $group['id']] = $roleId;

                $rawPermissions = $group['permission'] ?? null;
                $permList = [];
                if ($rawPermissions) {
                    $decoded = @unserialize($rawPermissions);
                    if (is_array($decoded)) {
                        $permList = $decoded;
                    }
                }

                foreach ($permList as $permName) {
                    if (! is_string($permName) || $permName === '') {
                        continue;
                    }

                    if (isset($permissionLookup[$permName])) {
                        $permId = $permissionLookup[$permName];
                    } else {
                        $existingPerm = $db->table('permissions')->where('name', $permName)->get()->getRowArray();
                        if ($existingPerm) {
                            $permId = (int) $existingPerm['id'];
                        } else {
                            $db->table('permissions')->insert([
                                'name' => $permName,
                                'module' => 'legacy',
                                'description' => null,
                            ]);
                            $permId = (int) $db->insertID();
                        }
                        $permissionLookup[$permName] = $permId;
                    }

                    $exists = $db->table('role_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $permId)
                        ->get()
                        ->getRowArray();
                    if (! $exists) {
                        $db->table('role_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permId,
                        ]);
                    }
                }
            }

            // Map users -> roles from legacy user_group table
            if ($db->tableExists('user_group')) {
                $userGroups = $db->table('user_group')->get()->getResultArray();
                foreach ($userGroups as $row) {
                    $roleId = $roleLookup[(int) $row['group_id']] ?? null;
                    if (! $roleId) {
                        continue;
                    }
                    $exists = $db->table('user_roles')
                        ->where('user_id', (int) $row['user_id'])
                        ->where('role_id', $roleId)
                        ->get()
                        ->getRowArray();
                    if (! $exists) {
                        $db->table('user_roles')->insert([
                            'user_id' => (int) $row['user_id'],
                            'role_id' => $roleId,
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('role_permissions')) {
            $this->forge->dropTable('role_permissions', true);
        }
        if ($db->tableExists('user_roles')) {
            $this->forge->dropTable('user_roles', true);
        }
        if ($db->tableExists('roles')) {
            $this->forge->dropTable('roles', true);
        }
        if ($db->tableExists('permissions')) {
            $this->forge->dropTable('permissions', true);
        }
        if ($db->tableExists('company_settings')) {
            $this->forge->dropTable('company_settings', true);
        }

        if ($db->tableExists('users')) {
            if ($db->fieldExists('first_name', 'users')) {
                $this->forge->dropColumn('users', 'first_name');
            }
            if ($db->fieldExists('last_name', 'users')) {
                $this->forge->dropColumn('users', 'last_name');
            }
            if ($db->fieldExists('remember_token', 'users')) {
                $this->forge->dropColumn('users', 'remember_token');
            }
            if ($db->fieldExists('reset_hash', 'users')) {
                $this->forge->dropColumn('users', 'reset_hash');
            }
            if ($db->fieldExists('reset_expires_at', 'users')) {
                $this->forge->dropColumn('users', 'reset_expires_at');
            }
            if ($db->fieldExists('avatar', 'users')) {
                $this->forge->dropColumn('users', 'avatar');
            }
        }

        if ($db->tableExists('notifications')) {
            if ($db->fieldExists('title', 'notifications')) {
                $this->forge->dropColumn('notifications', 'title');
            }
            if ($db->fieldExists('link', 'notifications')) {
                $this->forge->dropColumn('notifications', 'link');
            }
        }

        if ($db->tableExists('login_attempts') && $db->fieldExists('attempted_at', 'login_attempts')) {
            $db->query("ALTER TABLE login_attempts MODIFY attempted_at DATETIME NOT NULL");
        }
    }
}
