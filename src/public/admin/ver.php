<?php
// =====================================================================
// admin/ver.php  Ver unha necrolóxica tal como sairá na páxina pública
//
// Reutiliza a mesma tarxeta da páxina pública (tarxeta_esquela.php)
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

// O id debe ser un número enteiro positivo; se non, non se consulta
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

$n = null;
if ($id) {
    try {
        $n = obter_esquela(conectar_bd($config['bd']), $id);
    } catch (PDOException $ex) {
        error_log('EsquelasWeb (admin/ver): ' . $ex->getMessage());
        http_response_code(500);
        exit('Non foi posible consultar a necrolóxica.');
    }
}
if ($n === null) {
    header('Location: /admin/?msg=non_existe');
    exit;
}

$titulo = 'Ver necrolóxica';
require __DIR__ . '/../../include/cabeceira_admin.php';
?>
        <h2>Ver necrolóxica</h2>

        <?php if ($n['data_funeral'] < date('Y-m-d')): ?>
            <p class="aviso">O funeral xa pasou: esta necrolóxica non aparece na páxina pública.</p>
        <?php endif; ?>

        <div class="vista-previa">
            <?php require __DIR__ . '/../../include/tarxeta_esquela.php'; ?>
        </div>

        <p class="botons">
            <a class="boton" href="/admin/editar.php?id=<?= (int) $n['id'] ?>">Modificar</a>
            <a class="boton" href="/admin/eliminar.php?id=<?= (int) $n['id'] ?>">Eliminar</a>
            <a href="/admin/">Volver á listaxe</a>
        </p>
    </main>
</body>
</html>