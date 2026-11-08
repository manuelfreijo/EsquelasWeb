<?php
// =====================================================================
// admin/eliminar.php  Elimina unha necrolóxica
//
// GET:  amosa a necrolóxica e pide confirmación.
// POST: elimína a necrolóxica (o contido anterior queda na auditoría)
//       e volve á páxina principal (listaxe). 

// Só se elimina por POST: un enlace ou unha imaxe noutra web non poden
//  borrar nada, e a cookie SameSite=Strict impide que outra web envíe
//  o formulario coa sesión do editor.
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

// O id chega na URL (GET) ou nun campo oculto do formulario (POST)
$orixe = $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET;
$id = filter_input($orixe, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    header('Location: /admin/?msg=non_existe');
    exit;
}

try {
    $pdo = conectar_bd($config['bd']);

    // ---- POST: eliminar e volver á listaxe ----
    if ($orixe === INPUT_POST) {
        $feito = eliminar_esquela($pdo, $id, $usuario, ip_cliente());
        header('Location: /admin/?msg=' . ($feito ? 'eliminada' : 'non_existe'));
        exit;
    }

    // ---- GET: cargar a necrolóxica para amosala ----
    $n = obter_esquela($pdo, $id);
} catch (PDOException $ex) {
    error_log('EsquelasWeb (admin/eliminar): ' . $ex->getMessage());
    http_response_code(500);
    exit('Non foi posible completar a operación.');
}

if ($n === null) {
    header('Location: /admin/?msg=non_existe');
    exit;
}

$titulo = 'Eliminar necrolóxica';
require __DIR__ . '/../../include/cabeceira_admin.php';
?>
        <h2>Eliminar necrolóxica</h2>
        <p class="aviso erro">Vaise eliminar esta necrolóxica. Desaparecerá da páxina pública.</p>

        <div class="vista-previa">
            <?php require __DIR__ . '/../../include/tarxeta_esquela.php'; ?>
        </div>

        <form method="post" action="/admin/eliminar.php" class="botons">
            <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
            <button type="submit" class="perigo">Eliminar definitivamente</button>
            <a href="/admin/">Cancelar</a>
        </form>
    </main>
</body>
</html>