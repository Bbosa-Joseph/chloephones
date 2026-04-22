<?php

namespace App\Models;

class CompanySettingsModel extends BaseModel
{
    protected $table      = 'company_settings';
    protected $primaryKey = 'id';

    // No created_at on this table
    protected $useTimestamps = false;

    protected $allowedFields = [
        'company_name', 'address', 'phone', 'email', 'website',
        'country', 'currency', 'currency_symbol',
        'tax_rate', 'service_charge_rate',
        'logo', 'footer_message',
    ];

    /** Always read / update the single settings row (id = 1). */
    public function getSettings(): array
    {
        return $this->find(1) ?? [];
    }

    public function saveSettings(array $data): bool
    {
        $table = $this->db->table($this->table);
        $existing = $table->where('id', 1)->get()->getRowArray();
        if ($existing) {
            return (bool) $table->where('id', 1)->update($data);
        }

        $dataWithId = array_merge(['id' => 1], $data);
        return (bool) $table->insert($dataWithId);
    }
}
