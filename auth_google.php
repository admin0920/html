<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (!google_login_disponible()) {
    redirigir('login.php?google_error=1');
}

// Token anti-CSRF específico para el flujo de Google (protege el callback)
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$parametros = http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => url('auth_google_callback.php'),
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'prompt' => 'select_account',
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $parametros);
exit;
