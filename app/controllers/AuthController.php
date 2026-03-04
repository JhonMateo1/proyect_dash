<?php

require_once MODEL_PATH . '/AuditModel.php';
require_once MODEL_PATH . '/UserModel.php';
require_once MODEL_PATH . '/OTPModel.php';
require_once APP_PATH . '/services/MailerService.php';

class AuthController
{
    private function isNonGmailEmail($email)
    {
        $parts = explode('@', (string)$email);
        $domain = strtolower(trim($parts[1] ?? ''));

        return $domain !== 'gmail.com';
    }

    private function isPasswordValidForUser($plainPassword, $storedPassword)
    {
        if ($storedPassword === null || $storedPassword === '') {
            return false;
        }

        if (password_verify($plainPassword, $storedPassword)) {
            return true;
        }

        return hash_equals((string)$storedPassword, (string)$plainPassword);
    }

    private function passwordRules($password)
    {
        return [
            'length' => strlen($password) >= 8,
            'lower' => (bool)preg_match('/[a-z]/', $password),
            'upper' => (bool)preg_match('/[A-Z]/', $password),
            'number' => (bool)preg_match('/[0-9]/', $password),
            'special' => (bool)preg_match('/[^A-Za-z0-9]/', $password),
        ];
    }

    public function login()
    {
        require VIEW_PATH . '/login.php';
    }

    public function doLogin()
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user || !$this->isPasswordValidForUser($password, $user['password'] ?? null)) {
            $_SESSION['login_error'] = 'Credenciales incorrectas.';
            redirect(route('auth', 'login'));
        }

        $otpModel = new OTPModel();
        $otpCode = $otpModel->generate($email);

        $mailer = new MailerService();
        $otpSent = $mailer->sendOTP($email, (string)$otpCode);

        if (!$otpSent) {
            $_SESSION['login_error'] = 'No se pudo enviar el OTP al correo. Intenta nuevamente.';

            $audit = new AuditModel();
            $audit->log('EVENT_FAILED_OTP_DELIVERY', $email, 'Error enviando OTP por correo en login');
            redirect(route('auth', 'login'));
        }

        $_SESSION['otp_email'] = $email;

        $audit = new AuditModel();
        $audit->log('EVENT_LOGIN_ATTEMPT', $email, 'Credenciales válidas, OTP enviado por correo');

        redirect(route('auth', 'verify'));
    }

    public function verify()
    {
        require VIEW_PATH . '/verify.php';
    }

    public function verifyOTP()
    {
        $code = $_POST['code'];
        $email = $_SESSION['otp_email'] ?? null;

        if (!$email) {
            die("Sesión expirada. Inicie sesión nuevamente.");
        }

        $otpModel = new OTPModel();
        $isValid = $otpModel->verify($email, $code);

        if (!$isValid) {
            echo "❌ Código incorrecto o expirado<br>";
            echo "<a href='" . route('auth', 'verify') . "'>Intentar de nuevo</a>";
            $audit = new AuditModel();
            $audit->log('EVENT_FAILED_OTP', $email, 'Código incorrecto o expirado');
            return;
        }

        $audit = new AuditModel();
        $audit->log('EVENT_LOGIN', $email, 'Login exitoso');

        $_SESSION['user_id'] = $email;
        $_SESSION['is_non_gmail_user'] = $this->isNonGmailEmail($email);
        unset($_SESSION['otp_email']);

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!empty($user['must_change_password'])) {
            redirect(route('auth', 'changePasswordRequired'));
        }

        if (!empty($_SESSION['is_non_gmail_user'])) {
            redirect(route('dashboard', 'productsLanding'));
        }

        redirect(route('dashboard', 'index'));
    }

    public function changePasswordRequired()
    {
        authRequired();

        $email = $_SESSION['user_id'];
        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (empty($user['must_change_password'])) {
            redirect(route('dashboard', 'index'));
        }

        require VIEW_PATH . '/force_change_password.php';
    }

    public function updateMandatoryPassword()
    {
        authRequired();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(route('auth', 'changePasswordRequired'));
        }

        $email = $_SESSION['user_id'];
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $rules = $this->passwordRules($newPassword);
        $allRulesPassed = !in_array(false, $rules, true);

        if (!$allRulesPassed) {
            $_SESSION['password_change_error'] = 'La nueva contraseña no cumple con todos los requisitos de seguridad.';
            redirect(route('auth', 'changePasswordRequired'));
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['password_change_error'] = 'La confirmación de contraseña no coincide.';
            redirect(route('auth', 'changePasswordRequired'));
        }

        $userModel = new UserModel();
        $updated = $userModel->updatePasswordByEmail($email, $newPassword);

        if (!$updated) {
            $_SESSION['password_change_error'] = 'No se pudo actualizar la contraseña. Intenta nuevamente.';
            redirect(route('auth', 'changePasswordRequired'));
        }

        $audit = new AuditModel();
        $audit->log('EVENT_PASSWORD_CHANGED', $email, 'Cambio obligatorio de contraseña completado');

        $_SESSION['password_change_success'] = 'Contraseña actualizada correctamente.';

        redirect(route('dashboard', 'index'));
    }
}
