<?php
// =====================================================================
// esquelas.php  Operacións coa base de datos para a administración
//
// Regras que seguen todas as funcións:
//  - Consultas preparadas: os datos do formulario nunca se mesturan co
//    SQL.
//  - Cada cambio e o seu rexistro de auditoría van nunha TRANSACCIÓN:
//    gárdanse os dous ou ningún para garantír que non hai cambio sen
//    rexistro.
//  - Ó modificar ou eliminar, MariaDB copia antes a fila en JSON
//    (datos_anteriores) na mesma sentenza que escribe en auditoría.
// =====================================================================

/**
 * Columnas que se poden editar desde o formulario e
 * o seu tamaño máximo (null = sen límite de texto)
 * 
 */
const CAMPOS_ESQUELA = [
    'nome_completo'    => 200,
    'alcume'           => 100,
    'idade'            => null,
    'localidade'       => 100,
    'data_falecemento' => null,
    'lugar_velorio'    => 150,
    'data_funeral'     => null,
    'hora_funeral'     => null,
    'lugar_funeral'    => 150,
    'lugar_enterro'    => 150,
    'familiares'       => 2000,
    'mensaxe'          => 2000,
];

/** Campos que non poden estar baleiros. */
const CAMPOS_OBRIGATORIOS = ['nome_completo', 'data_falecemento', 'data_funeral',
                             'hora_funeral', 'lugar_funeral'];

/**
 * Listaxe para a páxina principal de administración
 * "vixente" vale 1 se o funeral é hoxe ou despois.
 */
function listar_esquelas(PDO $pdo): array
{
    $sql = 'SELECT id, nome_completo, alcume, data_funeral, hora_funeral,
                   lugar_funeral, (data_funeral >= CURDATE()) AS vixente
              FROM esquelas
             ORDER BY data_funeral DESC, hora_funeral DESC';
    return $pdo->query($sql)->fetchAll();
}

/**
 * Devolve unha necrolóxica completa ou null se non existe.
 */
function obter_esquela(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM esquelas WHERE id = :id');
    $st->execute(['id' => $id]);
    $fila = $st->fetch();
    return $fila === false ? null : $fila;
}

/**
 * Comproba os datos do formulario.
 * Devolve [datos limpos, lista de erros]. Se a lista de erros está
 * baleira, os datos pódense gardar (campos baleiros = null).
 */
function validar_esquela(array $entrada): array
{
    $datos = [];
    $erros = [];

    foreach (CAMPOS_ESQUELA as $campo => $maximo) {
        $valor = trim((string) ($entrada[$campo] ?? ''));
        $datos[$campo] = $valor === '' ? null : $valor;

        if ($datos[$campo] === null) {
            if (in_array($campo, CAMPOS_OBRIGATORIOS, true)) {
                $erros[] = 'O campo «' . etiqueta_campo($campo) . '» é obrigatorio.';
            }
            continue;
        }
        if ($maximo !== null && mb_strlen($valor) > $maximo) {
            $erros[] = 'O campo «' . etiqueta_campo($campo) . "» non pode superar $maximo caracteres.";
        }
    }

    // Idade: número enteiro entre 0 e 130
    if ($datos['idade'] !== null) {
        if (!ctype_digit($datos['idade']) || (int) $datos['idade'] > 130) {
            $erros[] = 'A idade debe ser un número entre 0 e 130.';
        } else {
            $datos['idade'] = (int) $datos['idade'];
        }
    }

    // Datas: formato AAAA-MM-DD e data real (non vale un 31 de febreiro)
    foreach (['data_falecemento', 'data_funeral'] as $campo) {
        if ($datos[$campo] !== null && !data_valida($datos[$campo])) {
            $erros[] = 'A data «' . etiqueta_campo($campo) . '» non é válida.';
            $datos[$campo] = null;
        }
    }

    // Hora: O navegador envía HH:MM e gárdase como HH:MM:00
    if ($datos['hora_funeral'] !== null) {
        if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $datos['hora_funeral']) !== 1) {
            $erros[] = 'A hora do funeral non é válida.';
        } else {
            $datos['hora_funeral'] .= ':00';
        }
    }

    // O funeral non pode ser anterior ó falecemento.
    // As datas con formato AAAA-MM-DD pódense comparar como texto.
    if ($datos['data_falecemento'] !== null && $datos['data_funeral'] !== null
        && $datos['data_funeral'] < $datos['data_falecemento']) {
        $erros[] = 'A data do funeral non pode ser anterior á do falecemento.';
    }

    return [$datos, $erros];
}

/** Comproba se é unha data real en formato AAAA-MM-DD. */
function data_valida(string $data): bool
{
    $d = DateTime::createFromFormat('!Y-m-d', $data);
    return $d !== false && $d->format('Y-m-d') === $data;
}

/** Nome dos campos como se amosan no formulario (para os erros). */
function etiqueta_campo(string $campo): string
{
    return [
        'nome_completo'    => 'Nome e apelidos',
        'alcume'           => 'Alcume',
        'idade'            => 'Idade',
        'localidade'       => 'Localidade',
        'data_falecemento' => 'Data do falecemento',
        'lugar_velorio'    => 'Velorio',
        'data_funeral'     => 'Data do funeral',
        'hora_funeral'     => 'Hora do funeral',
        'lugar_funeral'    => 'Lugar do funeral',
        'lugar_enterro'    => 'Enterro',
        'familiares'       => 'Familiares',
        'mensaxe'          => 'Mensaxe',
    ][$campo] ?? $campo;
}

/**
 * Expresión SQL para convertér unha fila de esquelas en JSON.
 * Constrúese só con nomes de columnas fixos do código, non con datos
 * do usuario.
 */
function json_fila_sql(): string
{
    $partes = [];
    foreach (array_keys(CAMPOS_ESQUELA) as $campo) {
        $partes[] = "'$campo', $campo";
    }
    return 'JSON_OBJECT(' . implode(', ', $partes) . ')';
}

/**
 * Crea unha necrolóxica e rexistra a acción. Devolve o novo id.
 */
function crear_esquela(PDO $pdo, array $datos, string $usuario, string $ip): int
{
    $columnas = array_keys(CAMPOS_ESQUELA);
    $sql = 'INSERT INTO esquelas (' . implode(', ', $columnas) . ')
            VALUES (:' . implode(', :', $columnas) . ')';

    $pdo->beginTransaction();
    try {
        $pdo->prepare($sql)->execute($datos);
        $id = (int) $pdo->lastInsertId();

        $pdo->prepare("INSERT INTO auditoria (usuario, accion, id_esquela, ip_orixe)
                       VALUES (:usuario, 'crear', :id, :ip)")
            ->execute(['usuario' => $usuario, 'id' => $id, 'ip' => $ip]);

        $pdo->commit();
        return $id;
    } catch (Throwable $ex) {
        $pdo->rollBack();   // desfai tamén o INSERT da necrolóxica
        throw $ex;
    }
}

/**
 * Modifica unha necrolóxica. Garda antes o seu contido en auditoria.
 * Devolve false se a necrolóxica xa non existe.
 */
function modificar_esquela(PDO $pdo, int $id, array $datos, string $usuario, string $ip): bool
{
    $asignacions = [];
    foreach (array_keys(CAMPOS_ESQUELA) as $campo) {
        $asignacions[] = "$campo = :$campo";
    }

    $pdo->beginTransaction();
    try {
        // 1. Copiar o contido actual á auditoría (se a fila existe)
        if (!rexistrar_contido_anterior($pdo, $id, 'modificar', $usuario, $ip)) {
            $pdo->rollBack();
            return false;
        }

        // 2. Aplicar o cambio
        $sql = 'UPDATE esquelas SET ' . implode(', ', $asignacions) . ' WHERE id = :id';
        $pdo->prepare($sql)->execute($datos + ['id' => $id]);

        $pdo->commit();
        return true;
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
}

/**
 * Elimina unha necrolóxica. Garda antes o seu contido en auditoria.
 * Devolve false se a necrolóxica xa non existe.
 */
function eliminar_esquela(PDO $pdo, int $id, string $usuario, string $ip): bool
{
    $pdo->beginTransaction();
    try {
        if (!rexistrar_contido_anterior($pdo, $id, 'eliminar', $usuario, $ip)) {
            $pdo->rollBack();
            return false;
        }
        $pdo->prepare('DELETE FROM esquelas WHERE id = :id')->execute(['id' => $id]);

        $pdo->commit();
        return true;
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
}

/**
 * Garda na auditoría o contido actual da necrolóxica en formato JSON.
 * É unha soa sentenza INSERT ... SELECT: MariaDB lee a fila e escribe
 * o rexistro.
 * Devolve false se a fila non existe (non se garda nada).
 * $accion só pode ser 'modificar' ou 'eliminar'.
 */
function rexistrar_contido_anterior(PDO $pdo, int $id, string $accion,
                                    string $usuario, string $ip): bool
{
    $sql = 'INSERT INTO auditoria (usuario, accion, id_esquela, ip_orixe, datos_anteriores)
            SELECT :usuario, :accion, id, :ip, ' . json_fila_sql() . '
              FROM esquelas WHERE id = :id';

    $st = $pdo->prepare($sql);
    $st->execute(['usuario' => $usuario, 'accion' => $accion, 'ip' => $ip, 'id' => $id]);
    return $st->rowCount() === 1;
}