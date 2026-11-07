<?php
// =====================================================================
// utilidades.php - Funcións para amosar datos
// =====================================================================

/**
 * Escapa un texto para mostralo en HTML.
 * Evita que un texto gardado na base de datos (por exemplo, "<script>")
 * se execute no navegador: inxección de código.
 * Úsarase SEMPRE que se mostre calquera dato.
 */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * "Escapar liñas": como e(), pero respectando os saltos de liña
 * (campos familiares e mensaxe, enderezo...).
 */
function el(?string $texto): string
{
    return nl2br(e($texto));
}

/**
 * Converte unha data da base de datos (AAAA-MM-DD) en texto lexible:
 * exemplo: "venres, 6 de novembro de 2026".
 */
function data_galego(string $data): string
{
    $dias  = ['domingo', 'luns', 'martes', 'mércores', 'xoves', 'venres', 'sábado'];
    $meses = ['xaneiro', 'febreiro', 'marzo', 'abril', 'maio', 'xuño',
              'xullo', 'agosto', 'setembro', 'outubro', 'novembro', 'decembro'];

    $d = DateTime::createFromFormat('Y-m-d', $data);
    if ($d === false) {
        return e($data);   // se o formato non é o esperado, amósase tal cal
    }

    return $dias[(int) $d->format('w')] . ', ' . $d->format('j') . ' de '
         . $meses[(int) $d->format('n') - 1] . ' de ' . $d->format('Y');
}

/**
 * Converte unha hora da base de datos (HH:MM:SS) en HH:MM.
 */
function hora_curta(string $hora): string
{
    return substr($hora, 0, 5);
}