<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController — foundation for every controller in the system.
 *
 * - Loads common helpers (form, url, cookie)
 * - Stores the session service
 * - Provides redirectIfAuthenticated() for public-only pages (login, forgot-password)
 *
 * Extend this for PUBLIC controllers (Auth).
 * Extend AppController for PROTECTED controllers (everything else).
 */
abstract class BaseController extends Controller
{
    protected $session;

    /**
     * Helpers available in every controller.
     * 'cookie' is required by remember-me and AuthFilter.
     */
    protected $helpers = ['form', 'url', 'cookie'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->session = service('session');
    }

    /**
     * Redirect already-authenticated users away from public pages (login, etc.).
     * Call at the top of any public action: if ($r = $this->redirectIfAuthenticated()) return $r;
     */
    protected function redirectIfAuthenticated(): ?ResponseInterface
    {
        if (session('logged_in') === true) {
            return redirect()->to(base_url('dashboard'));
        }
        return null;
    }
}

