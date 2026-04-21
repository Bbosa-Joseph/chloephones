<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Admin_Controller — backward-compatible bridge over AppController.
 *
 * Existing controllers continue to extend Admin_Controller unchanged.
 * Gradually migrate them to extend AppController directly as they are revised.
 */
class Admin_Controller extends AppController
{
    /** @deprecated Use $this->currentUser['id'] instead */
    protected $currentUserId = 0;

    protected bool $isAdmin = false;

    /** @deprecated Backward-compat alias for permissions list */
    protected array $permission = [];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->currentUserId        = $this->currentUser['id'] ?? 0;
        $this->isAdmin              = $this->currentUserId === 1;
        $this->data['is_admin']         = $this->isAdmin;
        $this->data['current_user_id']  = $this->currentUserId;

        // Backward-compat for legacy controllers/views
        $this->permission = (array) $this->permissions;
        $this->data['user_permission'] = (array) $this->permissions;
    }

    // ── Backward-compat guards ────────────────────────────────────────────────

    /** Redirect authenticated users (call on public-only pages). */
    protected function logged_in(): ?ResponseInterface
    {
        return $this->redirectIfAuthenticated();
    }

    /** Redirect unauthenticated users (belt-and-suspenders; filter handles this). */
    protected function not_logged_in(): ?ResponseInterface
    {
        if (session('logged_in') !== true) {
            return redirect()->to(base_url('auth/login'));
        }
        return null;
    }

    // ── Backward-compat template render ──────────────────────────────────────

    /** @deprecated Use return $this->render($page, $data); instead */
    protected function render_template(?string $page = null, array $data = []): void
    {
        echo $this->render($page ?? '', $data);
    }

    // ── Currency helpers ──────────────────────────────────────────────────────

    protected function company_currency(): string
    {
        $company = (new \App\Models\CompanySettingsModel())->getSettings();
        if (empty($company['currency'])) {
            return '';
        }
        return $this->currency()[$company['currency']] ?? '';
    }

    protected function currency(): array
    {
        return [
            'UGX' => '&#85;&#83;&#104;',
            'USD' => '&#36;',
            'EUR' => '&#8364;',
        ];
    }

    protected function currentUserId(): int
    {
        return $this->currentUserId;
    }

    protected function isAdminUser(): bool
    {
        return $this->isAdmin;
    }
}

