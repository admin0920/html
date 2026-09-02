<?php
require_once __DIR__ . '/db.php';

function usuario_actual(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = db_query_una('SELECT id, nombre, email, rol, plan_ritmo, modo_pro, avatar, puntos, racha_dias, ultima_actividad, creado_en, google_id FROM usuarios WHERE id = ?', 'i', [$_SESSION['usuario_id']]);
    return $cache;
}

function requiere_login(): void
{
    if (!usuario_actual()) {
        header('Location: ' . url('login.php?redirigido=1'));
        exit;
    }
}

function requiere_admin(): void
{
    $u = usuario_actual();
    if (!$u || $u['rol'] !== 'admin') {
        header('Location: ' . url('index.php'));
        exit;
    }
}

function es_admin(): bool
{
    $u = usuario_actual();
    return $u && $u['rol'] === 'admin';
}

function registrar_usuario(string $nombre, string $email, string $password): array
{
    $email = strtolower(trim($email));

    if (strlen($nombre) < 2) {
        return ['ok' => false, 'error' => 'El nombre es demasiado corto.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'El correo electrónico no es válido.'];
    }
    if (strlen($password) < 6) {
        return ['ok' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.'];
    }

    $existe = db_query_una('SELECT id FROM usuarios WHERE email = ?', 's', [$email]);
    if ($existe) {
        return ['ok' => false, 'error' => 'Ya existe una cuenta con ese correo.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $id = db_ejecutar(
        'INSERT INTO usuarios (nombre, email, password_hash, rol, ultima_actividad) VALUES (?, ?, ?, "usuario", CURDATE())',
        'sss',
        [$nombre, $email, $hash]
    );

    if ($id > 0) {
        $_SESSION['usuario_id'] = $id;
        return ['ok' => true];
    }

    return ['ok' => false, 'error' => 'No se pudo crear la cuenta. Intenta de nuevo.'];
}

function iniciar_sesion_usuario(string $email, string $password): array
{
    $email = strtolower(trim($email));
    $usuario = db_query_una('SELECT id, password_hash FROM usuarios WHERE email = ?', 's', [$email]);

    if (!$usuario || !$usuario['password_hash'] || !password_verify($password, $usuario['password_hash'])) {
        return ['ok' => false, 'error' => 'Correo o contraseña incorrectos. Si creaste tu cuenta con Google, usa el botón "Continuar con Google".'];
    }

    $_SESSION['usuario_id'] = $usuario['id'];
    actualizar_racha($usuario['id']);

    return ['ok' => true];
}

function cerrar_sesion_usuario(): void
{
    unset($_SESSION['usuario_id']);
    session_destroy();
}

function google_login_disponible(): bool
{
    return GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_SECRET !== '' && function_exists('curl_init');
}

/** Inicia sesión (o crea la cuenta) de un usuario autenticado con Google. */
function iniciar_sesion_con_google(string $googleId, string $email, string $nombre, ?string $avatar): array
{
    $email = strtolower(trim($email));

    $usuario = db_query_una('SELECT id FROM usuarios WHERE google_id = ?', 's', [$googleId]);

    if (!$usuario) {
        // ¿Ya existe una cuenta con ese correo (creada con contraseña)? La vinculamos.
        $usuario = db_query_una('SELECT id FROM usuarios WHERE email = ?', 's', [$email]);
        if ($usuario) {
            db_ejecutar('UPDATE usuarios SET google_id = ?, avatar = COALESCE(avatar, ?) WHERE id = ?', 'ssi', [$googleId, $avatar, $usuario['id']]);
        } else {
            $nombreFinal = $nombre !== '' ? $nombre : explode('@', $email)[0];
            $id = db_ejecutar(
                'INSERT INTO usuarios (nombre, email, password_hash, rol, google_id, avatar, ultima_actividad) VALUES (?, ?, NULL, "usuario", ?, ?, CURDATE())',
                'ssss',
                [$nombreFinal, $email, $googleId, $avatar]
            );
            $usuario = ['id' => $id];
        }
    }

    $_SESSION['usuario_id'] = $usuario['id'];
    actualizar_racha($usuario['id']);

    return ['ok' => true];
}

/** Actualiza la racha de días consecutivos de actividad del usuario */
function actualizar_racha(int $usuarioId): void
{
    $u = db_query_una('SELECT racha_dias, ultima_actividad FROM usuarios WHERE id = ?', 'i', [$usuarioId]);
    if (!$u) {
        return;
    }
    $hoy = date('Y-m-d');
    $ayer = date('Y-m-d', strtotime('-1 day'));

    if ($u['ultima_actividad'] === $hoy) {
        return; // ya contabilizado hoy
    }

    $nuevaRacha = ($u['ultima_actividad'] === $ayer) ? $u['racha_dias'] + 1 : 1;

    db_ejecutar('UPDATE usuarios SET racha_dias = ?, ultima_actividad = ? WHERE id = ?', 'isi', [$nuevaRacha, $hoy, $usuarioId]);
}

function sumar_puntos(int $usuarioId, int $puntos): void
{
    db_ejecutar('UPDATE usuarios SET puntos = puntos + ? WHERE id = ?', 'ii', [$puntos, $usuarioId]);
}
