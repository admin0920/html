<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (!google_login_disponible()) {
    redirigir('login.php?google_error=1');
}

// Validamos el token anti-CSRF y que Google haya devuelto un código
$state = $_GET['state'] ?? '';
$code = $_GET['code'] ?? '';
$estadoGuardado = $_SESSION['google_oauth_state'] ?? '';
unset($_SESSION['google_oauth_state']);

if ($code === '' || $state === '' || !hash_equals($estadoGuardado, $state)) {
    redirigir('login.php?google_error=1');
}

function google_peticion_json(string $url, array $opcionesCurl)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, $opcionesCurl + [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $respuesta = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($respuesta === false || $error) {
        return null;
    }
    $datos = json_decode($respuesta, true);
    return is_array($datos) ? $datos : null;
}

// 1. Intercambiamos el código de autorización por un access_token
$tokenRespuesta = google_peticion_json('https://oauth2.googleapis.com/token', [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => url('auth_google_callback.php'),
        'grant_type' => 'authorization_code',
    ]),
]);

if (!$tokenRespuesta || empty($tokenRespuesta['access_token'])) {
    redirigir('login.php?google_error=1');
}

// 2. Con el access_token, pedimos los datos del perfil del usuario
$perfil = google_peticion_json('https://www.googleapis.com/oauth2/v3/userinfo', [
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $tokenRespuesta['access_token']],
]);

if (!$perfil || empty($perfil['sub']) || empty($perfil['email'])) {
    redirigir('login.php?google_error=1');
}

if (empty($perfil['email_verified'])) {
    redirigir('login.php?google_error=2');
}

iniciar_sesion_con_google(
    $perfil['sub'],
    $perfil['email'],
    trim($perfil['name'] ?? ''),
    $perfil['picture'] ?? null
);

redirigir('perfil.php');
