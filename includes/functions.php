<?php
require_once __DIR__ . '/db.php';

function url(string $ruta = ''): string
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($ruta, '/');
}

function h(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function redirigir(string $ruta): void
{
    header('Location: ' . url($ruta));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_valido(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function obtener_cursos(bool $soloPublicados = true): array
{
    $sql = 'SELECT * FROM cursos' . ($soloPublicados ? ' WHERE publicado = 1' : '') . ' ORDER BY orden ASC';
    return db_query($sql);
}

function obtener_curso_por_slug(string $slug): ?array
{
    return db_query_una('SELECT * FROM cursos WHERE slug = ?', 's', [$slug]);
}

function obtener_modulos_con_lecciones(int $cursoId): array
{
    $modulos = db_query('SELECT * FROM modulos WHERE curso_id = ? ORDER BY orden ASC', 'i', [$cursoId]);
    foreach ($modulos as &$modulo) {
        $modulo['lecciones'] = db_query(
            'SELECT id, slug, titulo, orden, minutos_estimados FROM lecciones WHERE modulo_id = ? ORDER BY orden ASC',
            'i',
            [$modulo['id']]
        );
    }
    return $modulos;
}

function obtener_leccion_por_slug(string $slug): ?array
{
    return db_query_una(
        'SELECT l.*, m.curso_id, m.titulo AS modulo_titulo
         FROM lecciones l JOIN modulos m ON m.id = l.modulo_id
         WHERE l.slug = ?',
        's',
        [$slug]
    );
}

function leccion_completada(int $usuarioId, int $leccionId): bool
{
    $fila = db_query_una('SELECT id FROM progreso WHERE usuario_id = ? AND leccion_id = ?', 'ii', [$usuarioId, $leccionId]);
    return $fila !== null;
}

function marcar_leccion_completada(int $usuarioId, int $leccionId): void
{
    if (leccion_completada($usuarioId, $leccionId)) {
        return;
    }
    db_ejecutar('INSERT INTO progreso (usuario_id, leccion_id) VALUES (?, ?)', 'ii', [$usuarioId, $leccionId]);
    sumar_puntos($usuarioId, 10);
}

/** Progreso % de un usuario dentro de un curso */
function progreso_curso(int $usuarioId, int $cursoId): int
{
    $total = db_query_una(
        'SELECT COUNT(*) AS n FROM lecciones l JOIN modulos m ON m.id = l.modulo_id WHERE m.curso_id = ?',
        'i',
        [$cursoId]
    )['n'] ?? 0;

    if ($total == 0) {
        return 0;
    }

    $completadas = db_query_una(
        'SELECT COUNT(*) AS n FROM progreso p
         JOIN lecciones l ON l.id = p.leccion_id
         JOIN modulos m ON m.id = l.modulo_id
         WHERE m.curso_id = ? AND p.usuario_id = ?',
        'ii',
        [$cursoId, $usuarioId]
    )['n'] ?? 0;

    return (int) round(($completadas / $total) * 100);
}

/** Siguiente y anterior lección dentro de la secuencia global del curso, para navegación */
function lecciones_adyacentes(int $cursoId, int $ordenModulo, int $ordenLeccion): array
{
    $lecciones = db_query(
        'SELECT l.id, l.slug, l.titulo, m.orden AS orden_modulo, l.orden AS orden_leccion
         FROM lecciones l JOIN modulos m ON m.id = l.modulo_id
         WHERE m.curso_id = ?
         ORDER BY m.orden ASC, l.orden ASC',
        'i',
        [$cursoId]
    );

    $indiceActual = null;
    foreach ($lecciones as $i => $l) {
        if ($l['orden_modulo'] == $ordenModulo && $l['orden_leccion'] == $ordenLeccion) {
            $indiceActual = $i;
            break;
        }
    }

    if ($indiceActual === null) {
        return ['anterior' => null, 'siguiente' => null];
    }

    return [
        'anterior' => $lecciones[$indiceActual - 1] ?? null,
        'siguiente' => $lecciones[$indiceActual + 1] ?? null,
    ];
}

function tiempo_relativo(string $fecha): string
{
    $diff = time() - strtotime($fecha);
    if ($diff < 60) return 'hace un momento';
    if ($diff < 3600) return 'hace ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'hace ' . floor($diff / 3600) . ' h';
    return 'hace ' . floor($diff / 86400) . ' días';
}

/* ================================================================
 * ROADMAP / PLANES DE ESTUDIO / MODO PRO
 * ================================================================ */

/** Lecciones que se desbloquean por día según el plan elegido */
function ritmo_por_dia(string $plan): float
{
    return match ($plan) {
        'relajado' => 0.5,
        'intensivo' => 2.5,
        default => 1.0, // regular
    };
}

function nombre_plan(string $plan): string
{
    return match ($plan) {
        'relajado' => 'Relajado (1 lección cada 2 días)',
        'intensivo' => 'Intensivo (~2-3 lecciones por día)',
        default => 'Regular (1 lección por día)',
    };
}

/**
 * Devuelve, para un curso, la lista ordenada de lecciones con su estado:
 * 'completada', 'disponible' o 'bloqueada' (según el plan y fecha de inicio).
 */
function roadmap_curso(array $usuario, int $cursoId): array
{
    $lecciones = db_query(
        'SELECT l.id, l.slug, l.titulo, l.minutos_estimados, m.titulo AS modulo_titulo
         FROM lecciones l JOIN modulos m ON m.id = l.modulo_id
         WHERE m.curso_id = ? ORDER BY m.orden ASC, l.orden ASC',
        'i',
        [$cursoId]
    );

    $diasTranscurridos = max(0, (int) floor((time() - strtotime($usuario['creado_en'])) / 86400));
    $tasa = ritmo_por_dia($usuario['plan_ritmo']);
    $desbloqueadas = (int) floor($diasTranscurridos * $tasa) + 1;

    foreach ($lecciones as $i => &$l) {
        $l['completada'] = leccion_completada($usuario['id'], $l['id']);
        if ($l['completada']) {
            $l['estado'] = 'completada';
        } elseif ($usuario['modo_pro'] || ($i + 1) <= $desbloqueadas) {
            $l['estado'] = 'disponible';
        } else {
            $l['estado'] = 'bloqueada';
            $diasParaDesbloquear = (int) ceil((($i + 1) - $desbloqueadas) / max($tasa, 0.1));
            $l['dias_para_desbloquear'] = $diasParaDesbloquear;
        }
    }

    return $lecciones;
}

function actualizar_plan_usuario(int $usuarioId, string $plan): void
{
    if (!in_array($plan, ['relajado', 'regular', 'intensivo'], true)) {
        return;
    }
    db_ejecutar('UPDATE usuarios SET plan_ritmo = ? WHERE id = ?', 'si', [$plan, $usuarioId]);
}

function alternar_modo_pro(int $usuarioId, bool $activar): void
{
    db_ejecutar('UPDATE usuarios SET modo_pro = ? WHERE id = ?', 'ii', [$activar ? 1 : 0, $usuarioId]);
}

/* ================================================================
 * INSIGNIAS (logros)
 * ================================================================ */

function obtener_todas_insignias(): array
{
    return db_query('SELECT * FROM insignias ORDER BY id ASC');
}

function obtener_insignias_usuario(int $usuarioId): array
{
    return db_query(
        'SELECT i.*, ui.obtenida_en FROM usuario_insignias ui JOIN insignias i ON i.id = ui.insignia_id WHERE ui.usuario_id = ? ORDER BY ui.obtenida_en DESC',
        'i',
        [$usuarioId]
    );
}

function contar_lecciones_completadas(int $usuarioId): int
{
    return (int) (db_query_una('SELECT COUNT(*) AS n FROM progreso WHERE usuario_id = ?', 'i', [$usuarioId])['n'] ?? 0);
}

function contar_retos_completados(int $usuarioId): int
{
    return (int) (db_query_una('SELECT COUNT(*) AS n FROM reto_completados WHERE usuario_id = ?', 'i', [$usuarioId])['n'] ?? 0);
}

function contar_laboratorios_completados(int $usuarioId): int
{
    return (int) (db_query_una('SELECT COUNT(*) AS n FROM laboratorio_completados WHERE usuario_id = ?', 'i', [$usuarioId])['n'] ?? 0);
}

function contar_juegos_jugados(int $usuarioId): int
{
    return (int) (db_query_una('SELECT COUNT(*) AS n FROM juego_puntajes WHERE usuario_id = ?', 'i', [$usuarioId])['n'] ?? 0);
}

/** Revisa todas las condiciones y otorga las insignias nuevas que el usuario ya se ganó. Devuelve las insignias recién obtenidas. */
function evaluar_insignias(int $usuarioId): array
{
    $todas = obtener_todas_insignias();
    $yaObtenidas = array_column(db_query('SELECT insignia_id FROM usuario_insignias WHERE usuario_id = ?', 'i', [$usuarioId]), 'insignia_id');
    $usuario = db_query_una('SELECT * FROM usuarios WHERE id = ?', 'i', [$usuarioId]);
    $nuevas = [];

    $leccionesCompletadas = contar_lecciones_completadas($usuarioId);
    $retosCompletados = contar_retos_completados($usuarioId);
    $labsCompletados = contar_laboratorios_completados($usuarioId);
    $juegosJugados = contar_juegos_jugados($usuarioId);

    foreach ($todas as $insignia) {
        if (in_array($insignia['id'], $yaObtenidas, true)) {
            continue;
        }

        $cumple = match ($insignia['condicion_tipo']) {
            'lecciones_completadas' => $leccionesCompletadas >= $insignia['condicion_valor'],
            'racha_dias' => ($usuario['racha_dias'] ?? 0) >= $insignia['condicion_valor'],
            'retos_completados' => $retosCompletados >= $insignia['condicion_valor'],
            'laboratorios_completados' => $labsCompletados >= $insignia['condicion_valor'],
            'juegos_jugados' => $juegosJugados >= $insignia['condicion_valor'],
            'curso_completado' => $insignia['condicion_extra'] && curso_completado($usuarioId, $insignia['condicion_extra']),
            'todos_cursos' => todos_cursos_completados($usuarioId),
            default => false,
        };

        if ($cumple) {
            db_ejecutar('INSERT IGNORE INTO usuario_insignias (usuario_id, insignia_id) VALUES (?, ?)', 'ii', [$usuarioId, $insignia['id']]);
            sumar_puntos($usuarioId, (int) $insignia['puntos_bonus']);
            $nuevas[] = $insignia;
        }
    }

    return $nuevas;
}

function curso_completado(int $usuarioId, string $cursoSlug): bool
{
    $curso = obtener_curso_por_slug($cursoSlug);
    if (!$curso) {
        return false;
    }
    return progreso_curso($usuarioId, $curso['id']) >= 100;
}

function todos_cursos_completados(int $usuarioId): bool
{
    foreach (obtener_cursos() as $curso) {
        if (progreso_curso($usuarioId, $curso['id']) < 100) {
            return false;
        }
    }
    return true;
}

/* ================================================================
 * RETOS DE CÓDIGO
 * ================================================================ */

function obtener_retos(): array
{
    return db_query('SELECT * FROM retos ORDER BY orden ASC');
}

function obtener_reto_por_slug(string $slug): ?array
{
    return db_query_una('SELECT * FROM retos WHERE slug = ?', 's', [$slug]);
}

function reto_completado(int $usuarioId, int $retoId): bool
{
    return db_query_una('SELECT id FROM reto_completados WHERE usuario_id = ? AND reto_id = ?', 'ii', [$usuarioId, $retoId]) !== null;
}

function marcar_reto_completado(int $usuarioId, array $reto): bool
{
    if (reto_completado($usuarioId, $reto['id'])) {
        return false;
    }
    db_ejecutar('INSERT INTO reto_completados (usuario_id, reto_id) VALUES (?, ?)', 'ii', [$usuarioId, $reto['id']]);
    sumar_puntos($usuarioId, (int) $reto['puntos']);
    return true;
}

/* ================================================================
 * LABORATORIOS
 * ================================================================ */

function obtener_laboratorios(): array
{
    return db_query('SELECT * FROM laboratorios ORDER BY orden ASC');
}

function obtener_laboratorio_por_slug(string $slug): ?array
{
    return db_query_una('SELECT * FROM laboratorios WHERE slug = ?', 's', [$slug]);
}

function laboratorio_completado(int $usuarioId, int $labId): bool
{
    return db_query_una('SELECT id FROM laboratorio_completados WHERE usuario_id = ? AND laboratorio_id = ?', 'ii', [$usuarioId, $labId]) !== null;
}

function marcar_laboratorio_completado(int $usuarioId, array $lab): bool
{
    if (laboratorio_completado($usuarioId, $lab['id'])) {
        return false;
    }
    db_ejecutar('INSERT INTO laboratorio_completados (usuario_id, laboratorio_id) VALUES (?, ?)', 'ii', [$usuarioId, $lab['id']]);
    sumar_puntos($usuarioId, (int) $lab['puntos']);
    return true;
}

function obtener_solucion_laboratorio(int $usuarioId, int $labId): ?array
{
    return db_query_una('SELECT * FROM laboratorio_soluciones WHERE usuario_id = ? AND laboratorio_id = ?', 'ii', [$usuarioId, $labId]);
}

function guardar_solucion_laboratorio(int $usuarioId, int $labId, string $html, string $css, string $js): void
{
    $existe = obtener_solucion_laboratorio($usuarioId, $labId);
    if ($existe) {
        db_ejecutar('UPDATE laboratorio_soluciones SET html=?, css=?, js=? WHERE id=?', 'sssi', [$html, $css, $js, $existe['id']]);
    } else {
        db_ejecutar('INSERT INTO laboratorio_soluciones (usuario_id, laboratorio_id, html, css, js) VALUES (?,?,?,?,?)', 'iisss', [$usuarioId, $labId, $html, $css, $js]);
    }
}

/* ================================================================
 * JUEGOS (arcade)
 * ================================================================ */

function registrar_puntaje_juego(int $usuarioId, string $juego, int $puntaje): void
{
    db_ejecutar('INSERT INTO juego_puntajes (usuario_id, juego, puntaje) VALUES (?, ?, ?)', 'isi', [$usuarioId, $juego, $puntaje]);
}

function mejores_puntajes_juego(string $juego, int $limite = 5): array
{
    return db_query(
        'SELECT u.nombre, jp.puntaje, jp.creado_en FROM juego_puntajes jp JOIN usuarios u ON u.id = jp.usuario_id WHERE jp.juego = ? ORDER BY jp.puntaje DESC, jp.creado_en ASC LIMIT ?',
        'si',
        [$juego, $limite]
    );
}
