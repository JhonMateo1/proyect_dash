<?php
session_start();

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('MODEL_PATH', APP_PATH . '/models');
define('CONTROLLER_PATH', APP_PATH . '/controllers');
define('DATA_PATH', BASE_PATH . '/data');
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: 'no-reply@login-mvc.local');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Login MVC');
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', (int)(getenv('MAIL_PORT') ?: 587));
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls');
define('MAIL_AUTH', filter_var(getenv('MAIL_AUTH') ?: 'true', FILTER_VALIDATE_BOOLEAN));


function redirect($url)
{
    header("Location: $url");
    exit;
}

function route($controller, $action)
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    return $script . '?controller=' . urlencode($controller) . '&action=' . urlencode($action);
}

function asset($file)
{
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    if ($scriptDir === '.' || $scriptDir === DIRECTORY_SEPARATOR) {
        $scriptDir = '';
    }

    return rtrim($scriptDir, '/\\') . '/' . ltrim($file, '/');
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);

}

date_default_timezone_set('America/Lima');

function authRequired()
{
    if (!isset($_SESSION['user_id'])) {
        redirect(route('auth', 'login'));
    }
}
