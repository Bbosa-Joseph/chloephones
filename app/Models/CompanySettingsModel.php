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
        return $this->update(1, $data);
    }
}
