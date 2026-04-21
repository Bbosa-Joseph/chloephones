<?php

namespace App\Controllers;

use App\Models\CompanySettingsModel;
use App\Models\Model_company;

class Controller_Company extends Admin_Controller
{
    public function __construct()
    {
        $this->not_logged_in();
        $this->data['page_title'] = 'Company';
    }

    public function index()
    {
        if (!in_array('updateCompany', $this->permission)) {
            return redirect()->to(base_url('dashboard'));
        }

        $db = \Config\Database::connect();
        $hasSettingsTable = $db->tableExists('company_settings');
        $settingsModel = new CompanySettingsModel();
        $legacyModel = new Model_company();

        if ($this->request->getMethod() === 'post') {
            $payload = [
                'company_name' => $this->request->getPost('company_name'),
                'address' => $this->request->getPost('address'),
                'phone' => $this->request->getPost('phone'),
                'email' => $this->request->getPost('email'),
                'website' => $this->request->getPost('website'),
                'country' => $this->request->getPost('country'),
                'currency' => $this->request->getPost('currency'),
                'currency_symbol' => $this->request->getPost('currency_symbol'),
                'tax_rate' => (float) $this->request->getPost('tax_rate'),
                'service_charge_rate' => (float) $this->request->getPost('service_charge_rate'),
                'footer_message' => $this->request->getPost('footer_message'),
            ];

            if ($hasSettingsTable) {
                $settingsModel->saveSettings($payload);
            }

            if ($db->tableExists('company')) {
                $legacyModel->updateCompany([
                    'company_name' => $payload['company_name'],
                    'currency' => $payload['currency'],
                    'vat_charge_value' => $payload['tax_rate'],
                    'service_charge_value' => $payload['service_charge_rate'],
                ], 1);
            }

            session()->setFlashdata('success', 'Company settings updated.');
            return redirect()->to(base_url('Controller_Company'));
        }

        $companyData = [];
        if ($hasSettingsTable) {
            $companyData = $settingsModel->getSettings();
        }

        if (empty($companyData) && $db->tableExists('company')) {
            $legacy = $legacyModel->getCompanyData(1);
            if ($legacy) {
                $companyData = [
                    'company_name' => $legacy['company_name'] ?? '',
                    'currency' => $legacy['currency'] ?? '',
                    'tax_rate' => $legacy['vat_charge_value'] ?? 0,
                    'service_charge_rate' => $legacy['service_charge_value'] ?? 0,
                ];
            }
        }

        $this->data['company_data'] = $companyData;
        return $this->render_template('company/index', $this->data);
    }
}
