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
