<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * AppController — base class for ALL protected controllers.
 *
 * AuthFilter runs before any method here and guarantees a valid session
 * (or a restored remember-me session).  AppController reads that session
 * and makes user data + RBAC permissions available throughout the app.
 *
 * Usage:
 *   class Dashboard extends AppController { ... }
 *
 * Template rendering:
 *   return $this->render('dashboard/index', $data);
 *
 * Permission gate:
 *   if (! $this->hasPermission('orders.create')) return $this->deny();
 */
abstract class AppController extends BaseController
{
    /** Authenticated user data (from session). */
    protected array $currentUser = [];

    /** Flat list of permission names granted to the current user. */
    protected array $permissions = [];

    /** Shared view data merged into every render() call. */
    protected array $data = [];

    // -------------------------------------------------------------------------

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        // AuthFilter guarantees session is active before we reach here.
        if (session('logged_in') === true) {
            $this->currentUser = [
                'id'         => (int) session('user_id'),
                'username'   => (string) session('username'),
                'email'      => (string) session('email'),
                'first_name' => (string) session('first_name'),
                'last_name'  => (string) session('last_name'),
            ];

            // Always reload permissions so recent changes take effect immediately.
            $this->permissions = (new UserModel())->getPermissions($this->currentUser['id']);
            session()->set('permissions', $this->permissions);

            $this->data = [
                'current_user' => $this->currentUser,
                'permissions'  => $this->permissions,
            ];
        }
    }

    // ── RBAC helpers ──────────────────────────────────────────────────────────

    /**
     * True if the authenticated user holds the named permission.
     * Permission name convention: '{module}.{action}'  e.g. 'orders.create'
     */
    protected function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    /**
     * Return a 403 response for unauthorised actions.
     * Controllers may override this to show a custom view.
     */
    protected function deny(string $message = 'You do not have permission to perform this action.'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(403)
            ->setBody(view('errors/html/error_403', ['message' => $message]));
    }

    // ── Template rendering ────────────────────────────────────────────────────

    /**
     * Render a view inside the shared admin template.
     * $data is merged with $this->data (current_user, permissions, etc.).
     */
    protected function render(string $view, array $data = []): string
    {
        $payload  = array_merge($this->data, $data);
        $output   = view('templates/header',       $payload);
        $output  .= view('templates/header_menu',  $payload);
        $output  .= view('templates/side_menubar', $payload);
        $output  .= view($view,                    $payload);
        $output  .= view('templates/footer',       $payload);
        return $output;
    }
}
