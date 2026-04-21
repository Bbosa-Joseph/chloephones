<?php

namespace App\Controllers;

use App\Models\LoginAttemptModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Auth controller — handles all public authentication routes.
 *
 * Extends BaseController (NOT AppController) so no auth guard runs here.
 *
 * Routes (all public, no filter):
 *   GET  auth/login                       → login()
 *   POST auth/login                       → authenticate()
 *   GET  auth/logout                      → logout()
 *   GET  auth/forgot-password             → forgotPassword()
 *   POST auth/forgot-password             → sendResetLink()
 *   GET  auth/reset-password/(:alphanum)  → resetPassword($token)
 *   POST auth/reset-password              → doResetPassword()
 */
class Auth extends BaseController
{
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_MINUTES = 15;
    private const RESET_EXPIRY    = 60;  // minutes
    private const REMEMBER_DAYS   = 30;

    // =========================================================================
    // LOGIN
    // =========================================================================

    public function login(): string|ResponseInterface
    {
        if ($r = $this->redirectIfAuthenticated()) {
            return $r;
        }
        return view('login', [
            'success' => session()->getFlashdata('success'),
            'error'   => session()->getFlashdata('error'),
        ]);
    }

    public function authenticate(): string|ResponseInterface
    {
        if ($r = $this->redirectIfAuthenticated()) {
            return $r;
        }

        $rules = [
            'email'    => 'required|valid_email|max_length[254]',
            'password' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return view('login', [
                'errors' => $this->validator->getErrors(),
                'old'    => ['email' => $this->request->getPost('email')],
            ]);
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $ip       = $this->request->getIPAddress();

        $attemptModel = new LoginAttemptModel();

        // Brute-force guard
        if ($attemptModel->countRecent($ip, $email, self::LOCKOUT_MINUTES) >= self::MAX_ATTEMPTS) {
            return view('login', [
                'error' => 'Too many failed login attempts. Please try again in '
                         . self::LOCKOUT_MINUTES . ' minutes.',
                'old'   => ['email' => $email],
            ]);
        }

        $userModel = new UserModel();
        $user      = $userModel->findForAuth($email);

        // Constant-time failure — same message for unknown email and wrong password
        if (! $user || ! password_verify($password, $user['password'])) {
            $attemptModel->insert(['ip_address' => $ip, 'email' => $email]);
            log_message('notice', "Failed login for {$email} from {$ip}");
            return view('login', [
                'error' => 'Invalid email or password.',
                'old'   => ['email' => $email],
            ]);
        }

        if (! $user['is_active']) {
            return view('login', [
                'error' => 'Your account is disabled. Contact an administrator.',
            ]);
        }

        // ── Success ───────────────────────────────────────────────────────────
        $permissions = $userModel->getPermissions($user['id']);
        $userModel->touchLogin($user['id']);

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

        // Remember me (30-day rolling token)
        if ($this->request->getPost('remember_me')) {
            $rawToken = bin2hex(random_bytes(32));
            $userModel->update($user['id'], ['remember_token' => hash('sha256', $rawToken)]);
            set_cookie([
                'name'     => 'remember_me',
                'value'    => $rawToken,
                'expire'   => self::REMEMBER_DAYS * DAY,
                'httponly' => true,
                'secure'   => (ENVIRONMENT !== 'development'),
                'samesite' => 'Lax',
            ]);
        }

        return redirect()->to(base_url('dashboard'));
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================

    public function logout(): ResponseInterface
    {
        $userId = session('user_id');

        if ($userId) {
            $rawToken = get_cookie('remember_me');
            if ($rawToken) {
                // Revoke the stored token so the cookie cannot be reused
                (new UserModel())->update($userId, ['remember_token' => null]);
            }
        }

        delete_cookie('remember_me');
        session()->destroy();

        return redirect()->to(base_url('auth/login'));
    }

    // =========================================================================
    // FORGOT PASSWORD
    // =========================================================================

    public function forgotPassword(): string|ResponseInterface
    {
        if ($r = $this->redirectIfAuthenticated()) {
            return $r;
        }
        return view('auth/forgot_password', [
            'success' => session()->getFlashdata('success'),
            'error'   => session()->getFlashdata('error'),
        ]);
    }

    public function sendResetLink(): ResponseInterface
    {
        if ($r = $this->redirectIfAuthenticated()) {
            return $r;
        }

        if (! $this->validate(['email' => 'required|valid_email|max_length[254]'])) {
            return view('auth/forgot_password', [
                'errors' => $this->validator->getErrors(),
                'old'    => ['email' => $this->request->getPost('email')],
            ]);
        }

        $email     = $this->request->getPost('email');
        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->where('is_active', 1)->first();

        // Always show the same message to prevent email enumeration
        if ($user) {
            $rawToken    = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $rawToken);
            $expiry      = date('Y-m-d H:i:s', strtotime('+' . self::RESET_EXPIRY . ' minutes'));

            $userModel->update($user['id'], [
                'reset_hash'       => $hashedToken,
                'reset_expires_at' => $expiry,
            ]);

            $this->sendPasswordResetEmail($user, base_url('auth/reset-password/' . $rawToken));
        }

        return redirect()
            ->to(base_url('auth/forgot-password'))
            ->with('success', 'If that email is registered you will receive a reset link shortly.');
    }

    // =========================================================================
    // RESET PASSWORD
    // =========================================================================

    public function resetPassword(string $token): string|ResponseInterface
    {
        if ($r = $this->redirectIfAuthenticated()) {
            return $r;
        }

        // Validate token before showing the form
        $user = (new UserModel())->findByResetHash(hash('sha256', $token));

        if (! $user) {
            return redirect()
                ->to(base_url('auth/forgot-password'))
                ->with('error', 'This reset link is invalid or has expired. Please request a new one.');
        }

        return view('auth/reset_password', [
            'token'  => $token,
            'errors' => session()->getFlashdata('errors'),
        ]);
    }

    public function doResetPassword(): ResponseInterface
    {
        $rules = [
            'token'                 => 'required',
            'password'              => 'required|min_length[8]|max_length[255]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $token     = $this->request->getPost('token');
        $userModel = new UserModel();
        $user      = $userModel->findByResetHash(hash('sha256', $token));

        if (! $user) {
            return redirect()
                ->to(base_url('auth/forgot-password'))
                ->with('error', 'This reset link is invalid or has expired.');
        }

        // Update password (hashPassword callback fires); clear reset fields.
        // skipValidation so is_unique / required rules don't block partial update.
        $userModel->skipValidation(true)->update($user['id'], [
            'password'         => $this->request->getPost('password'),
            'reset_hash'       => null,
            'reset_expires_at' => null,
        ]);

        log_message('info', "Password reset completed for user ID {$user['id']}");

        return redirect()
            ->to(base_url('auth/login'))
            ->with('success', 'Password updated successfully. Please log in.');
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function sendPasswordResetEmail(array $user, string $resetLink): void
    {
        try {
            $emailService = service('email');
            $emailService->setTo($user['email']);
            $emailService->setSubject('Password Reset Request — ChloePhones');
            $emailService->setMessage(view('emails/password_reset', [
                'user'       => $user,
                'reset_link' => $resetLink,
                'expiry_min' => self::RESET_EXPIRY,
            ]));
            $emailService->send();
        } catch (\Throwable $e) {
            log_message('error', 'Password reset email failed for ' . $user['email'] . ': ' . $e->getMessage());
        }
    }
}

