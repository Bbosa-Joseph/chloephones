<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter — guards every protected route.
 *
 * Priority of checks:
 *   1. Valid session  → pass through
 *   2. remember_me cookie with a matching DB token → restore session, roll token, pass through
 *   3. Nothing valid  → redirect to login
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Session already active
        if (session('logged_in') === true) {
            return;
        }

        // 2. Try remember-me cookie
        helper('cookie');
        $rawToken = get_cookie('remember_me');

        if ($rawToken) {
            $userModel = new UserModel();
            $user      = $userModel->findByRememberToken(hash('sha256', $rawToken));

            if ($user) {
                $permissions = $userModel->getPermissions($user['id']);
                $userModel->touchLogin($user['id']);

                // Roll the token on every use (sliding window, prevents replay)
                $newRaw = bin2hex(random_bytes(32));
                $userModel->update($user['id'], ['remember_token' => hash('sha256', $newRaw)]);

                set_cookie([
                    'name'     => 'remember_me',
                    'value'    => $newRaw,
                    'expire'   => 30 * DAY,
                    'httponly' => true,
                    'secure'   => (ENVIRONMENT !== 'development'),
                    'samesite' => 'Lax',
                ]);

                $firstName = $user['first_name'] ?? $user['firstname'] ?? '';
                $lastName  = $user['last_name'] ?? $user['lastname'] ?? '';

                session()->set([
                    'user_id'     => $user['id'],
                    'username'    => $user['username'],
                    'email'       => $user['email'],
                    'first_name'  => $firstName,
                    'last_name'   => $lastName,
                    'permissions' => $permissions,
                    'logged_in'   => true,
                ]);

                return;
            }
        }

        // 3. Not authenticated
        session()->setFlashdata('error', 'Please log in to continue.');
        return redirect()->to(base_url('auth/login'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // nothing needed
    }
}
