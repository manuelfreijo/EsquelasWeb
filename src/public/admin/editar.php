<?php
// =====================================================================
// admin/editar.php  Formulario para crear ou modificar unha necrolóxica
//
// O mesmo formulario serve para as dúas accións:
//   sen id  -> nova necrolóxica
//   con id  -> modificar a existente (o id vai nun campo oculto)
//
// GET:  amosa o formulario (baleiro ou cos datos actuais).
// POST: valida os datos no servidor. Se hai erros, volve amosar o
//       contido no formulario e a lista de erros. Se todo está ben,
//       garda e redirixe á listaxe.
// =====================================================================

declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
require __DIR__ . '/../../include/utilidades.php';
require __DIR__ . '/../../include/sesion.php';
require __DIR__ . '/../../include/bd.php';
require __DIR__ . '/../../include/esquelas.php';

$usuario = requirir_login($config['sesion']['duracion']);
non_gardar_en_cache();

$funeraria = $config['funeraria'];

// id: na URL ó abrir o formulario e no campo oculto ó enviarlo.
// null = nova necrolóxica.
$orixe = $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET;
$id = filter_input($orixe, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

// Valores e erros que se amosan no formulario.
$valores = [];
$erros   = [];

try {
    $pdo = conectar_bd($config['bd']);

    if ($orixe === INPUT_POST) {
        // ---- POST: validar e gardar ----
        [$datos, $erros] = validar_esquela($_POST);

        if (count($erros) === 0) {
            if ($id === null) {
                crear_esquela($pdo, $datos, $usuario, ip_cliente());
                $msg = 'gardada';
            } else {
                $msg = modificar_esquela($pdo, $id, $datos, $usuario, ip_cliente())
                     ? 'gardada' : 'non_existe';
            }
            header('Location: /admin/?msg=' . $msg);
            exit;
        }
        // Con erros: volver amosar exactamente o que xa estaba escrito.
        foreach (array_keys(CAMPOS_ESQUELA) as $campo) {
            $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
        }
    } elseif ($id !== null) {
        // ---- GET con id: cargar os datos actuais de novo 
        $fila = obter_esquela($pdo, $id);
        if ($fila === null) {
            header('Location: /admin/?msg=non_existe');
            exit;
        }
        foreach (array_keys(CAMPOS_ESQUELA) as $campo) {
            $valores[$campo] = (string) ($fila[$campo] ?? '');
        }
        // O campo de hora do navegador usa HH:MM (non ten segundos)
        $valores['hora_funeral'] = hora_curta($valores['hora_funeral']);
    }
} catch (PDOException $ex) {
    error_log('EsquelasWeb (admin/editar): ' . $ex->getMessage());
    $erros[] = 'Non foi posible gardar na base de datos. Ténteo de novo máis tarde.';
    foreach (array_keys(CAMPOS_ESQUELA) as $campo) {
        $valores[$campo] = trim((string) ($_POST[$campo] ?? $valores[$campo] ?? ''));
    }
}

// Valor dun campo xa escapado para poñelo no atributo value=""
function v(array $valores, string $campo): string
{
    return e($valores[$campo] ?? '');
}

$titulo = $id === null ? 'Nova necrolóxica' : 'Modificar necrolóxica';
require __DIR__ . '/../../include/cabeceira_admin.php';
?>
        <h2><?= e($titulo) ?></h2>

        <?php if (count($erros) > 0): ?>
            <div class="aviso erro">
                <p>Revise os seguintes datos:</p>
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?= e($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- required, maxlength, type="date"... axudan ao editor no
             navegador, pero a comprobación que realmente conta é a do servidor -->
        <form method="post" action="/admin/editar.php" class="formulario">
            <?php if ($id !== null): ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
            <?php endif; ?>

            <fieldset>
                <legend>Persoa falecida</legend>

                <label for="nome_completo">Nome e apelidos *</label>
                <input type="text" id="nome_completo" name="nome_completo" maxlength="200" required
                       value="<?= v($valores, 'nome_completo') ?>">

                <div class="fila">
                    <div>
                        <label for="alcume">Alcume</label>
                        <input type="text" id="alcume" name="alcume" maxlength="100"
                               value="<?= v($valores, 'alcume') ?>">
                    </div>
                    <div>
                        <label for="idade">Idade</label>
                        <input type="number" id="idade" name="idade" min="0" max="130"
                               value="<?= v($valores, 'idade') ?>">
                    </div>
                </div>

                <div class="fila">
                    <div>
                        <label for="localidade">Localidade</label>
                        <input type="text" id="localidade" name="localidade" maxlength="100"
                               value="<?= v($valores, 'localidade') ?>">
                    </div>
                    <div>
                        <label for="data_falecemento">Data do falecemento *</label>
                        <input type="date" id="data_falecemento" name="data_falecemento" required
                               value="<?= v($valores, 'data_falecemento') ?>">
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Servizos</legend>

                <label for="lugar_velorio">Velorio</label>
                <input type="text" id="lugar_velorio" name="lugar_velorio" maxlength="150"
                       value="<?= v($valores, 'lugar_velorio') ?>">

                <div class="fila">
                    <div>
                        <label for="data_funeral">Data do funeral *</label>
                        <input type="date" id="data_funeral" name="data_funeral" required
                               value="<?= v($valores, 'data_funeral') ?>">
                    </div>
                    <div>
                        <label for="hora_funeral">Hora do funeral *</label>
                        <input type="time" id="hora_funeral" name="hora_funeral" required
                               value="<?= v($valores, 'hora_funeral') ?>">
                    </div>
                </div>

                <label for="lugar_funeral">Lugar do funeral *</label>
                <input type="text" id="lugar_funeral" name="lugar_funeral" maxlength="150" required
                       value="<?= v($valores, 'lugar_funeral') ?>">

                <label for="lugar_enterro">Enterro</label>
                <input type="text" id="lugar_enterro" name="lugar_enterro" maxlength="150"
                       value="<?= v($valores, 'lugar_enterro') ?>">
            </fieldset>

            <fieldset>
                <legend>Texto da necrolóxica</legend>

                <label for="familiares">Familiares</label>
                <textarea id="familiares" name="familiares" rows="4" maxlength="2000"><?= v($valores, 'familiares') ?></textarea>

                <label for="mensaxe">Mensaxe</label>
                <textarea id="mensaxe" name="mensaxe" rows="3" maxlength="2000"><?= v($valores, 'mensaxe') ?></textarea>
            </fieldset>

            <p class="nota">Os campos marcados con * son obrigatorios.</p>

            <p class="botons">
                <button type="submit">Gardar</button>
                <a href="/admin/">Cancelar</a>
            </p>
        </form>
    </main>
</body>
</html>