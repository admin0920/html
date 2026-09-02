<?php
require_once __DIR__ . '/../config/config.php';

function db(): mysqli
{
    static $conexion = null;

    if ($conexion === null) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conexion = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conexion->connect_error) {
            http_response_code(500);
            if (MODO_DEBUG) {
                die('Error de conexión a la base de datos: ' . $conexion->connect_error);
            }
            die('No se pudo conectar a la base de datos. Revisa config/config.php e importa config/database.sql.');
        }

        $conexion->set_charset('utf8mb4');
    }

    return $conexion;
}

/**
 * Ejecuta una consulta preparada y devuelve todas las filas como array asociativo.
 * $tipos: cadena de tipos para bind_param (ej. "si" = string, int)
 */
function db_query(string $sql, string $tipos = '', array $params = []): array
{
    $conn = db();
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        if (MODO_DEBUG) {
            die('Error SQL: ' . $conn->error . ' | ' . $sql);
        }
        return [];
    }
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    $filas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $filas;
}

/** Devuelve solo la primera fila (o null) */
function db_query_una(string $sql, string $tipos = '', array $params = []): ?array
{
    $filas = db_query($sql, $tipos, $params);
    return $filas[0] ?? null;
}

/**
 * Ejecuta INSERT/UPDATE/DELETE. Devuelve el insert_id en inserts,
 * o el número de filas afectadas.
 */
function db_ejecutar(string $sql, string $tipos = '', array $params = []): int
{
    $conn = db();
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        if (MODO_DEBUG) {
            die('Error SQL: ' . $conn->error . ' | ' . $sql);
        }
        return 0;
    }
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $id = $conn->insert_id ?: $stmt->affected_rows;
    $stmt->close();
    return (int) $id;
}
