<?php
// =====================================================================
// bd.php  Conexión coa base de datos MariaDB usando PDO
// =====================================================================

/**
 * Abre unha conexión coa base de datos a partir da configuración.
 *
 * - charset=utf8mb4: tiles, «ñ» e calquera carácter.
 * - ERRMODE_EXCEPTION: Para que se loguen excepicions en cado de erro.
 * - FETCH_ASSOC: cada fila devólve un array co nome das columnas.
 * - EMULATE_PREPARES = false: as consultas preparadas execútaas a base
 *   de datos, para protexer contra a inxección de código SQL.
 */

function conectar_bd(array $c): PDO
{
    $dsn = "mysql:host={$c['host']};dbname={$c['nome']};charset=utf8mb4";

    return new PDO($dsn, $c['usuario'], $c['contrasinal'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}