<?php
/**
 * Configuración general de "Dique Programando".
 * Edita los datos de la base de datos con los que InfinityFree
 * te entrega en el vPanel (MySQL Databases).
 */

// ------------------------------------------------------------------
// Datos de conexión a MySQL (InfinityFree)
// Ejemplo típico de InfinityFree:
//   host: sqlXXX.infinityfree.com
//   user: if0_XXXXXXXX
//   pass: la que definiste al crear la base de datos
//   name: if0_XXXXXXXX_diqueprogramando
// ------------------------------------------------------------------
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_USER', 'if0_XXXXXXXX');
define('DB_PASS', 'TU_PASSWORD_AQUI');
define('DB_NAME', 'if0_XXXXXXXX_diqueprogramando');

// URL base del sitio (sin barra final). En InfinityFree suele ser tu subdominio,
// por ejemplo: https://diqueprogramando.rf.gd
define('SITE_URL', 'https://tudominio.infinityfreeapp.com');
define('SITE_NAME', 'Dique Programando');

// ------------------------------------------------------------------
// Inicio de sesión con Google (OAuth 2.0)
// Crea credenciales gratis en https://console.cloud.google.com/apis/credentials
//   1. Crea un proyecto y una pantalla de consentimiento OAuth (tipo "Externo").
//   2. Crea credenciales -> ID de cliente de OAuth -> tipo "Aplicación web".
//   3. En "URIs de redireccionamiento autorizados" agrega EXACTAMENTE:
//      SITE_URL + /auth_google_callback.php
//      (con la SITE_URL real de arriba, por ejemplo:
//      https://tudominio.infinityfreeapp.com/auth_google_callback.php)
//   4. Copia el Client ID y Client Secret aquí abajo.
// Nota: InfinityFree (plan gratuito) a veces bloquea las conexiones salientes
// (cURL) que este flujo necesita para hablar con Google. Si el botón de
// Google no funciona en tu hosting, ese suele ser el motivo: prueba con un
// plan que permita conexiones salientes, o deja esta función desactivada
// (simplemente no la actives desde el panel de Google y el resto del sitio
// sigue funcionando normal con registro por correo/contraseña).
// ------------------------------------------------------------------
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');

// Zona horaria
date_default_timezone_set('America/Bogota');

// Reporte de errores: en producción déjalo en 0 para no exponer datos sensibles.
define('MODO_DEBUG', false);
if (MODO_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Sesiones
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
