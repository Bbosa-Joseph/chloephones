<?php

namespace App\Models;

use CodeIgniter\Model;

class Model_company extends Model
{
    protected $table = 'company';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['company_name', 'service_charge_value', 'vat_charge_value', 'currency'];

    public function getCompanyData($id = null)
    {
        if ($id) {
            return $this->where('id', $id)->first();
        }

        return null;
    }

    public function updateCompany(array $data, $id): bool
    {
        return $this->update($id, $data);
    }
}
