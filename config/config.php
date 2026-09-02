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
