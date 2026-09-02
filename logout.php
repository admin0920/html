<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

cerrar_sesion_usuario();
redirigir('index.php');
