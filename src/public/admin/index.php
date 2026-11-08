<?php
// =====================================================================
// admin/index.php - Inicio da parte de administración
//
// Dede aqui faise a xestión das necrolóxicas.
// Só cos campos que axudan a localizar cada unha.
// Amosa as necrolóxicas (vixentes e pasadas), 
// coas máis recentes primeiro e
// Botóns para ver, modificar, eliminar ou crear unha nova. 
// =====================================================================

declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
require __DIR__ . '/../../include/utilidades.php';
require __DIR__ . '/../../include/sesion.php';
require __DIR__ . '/../../include/bd.php';
require __DIR__ . '/../../include/esquelas.php';

// Sen sesión válida envía a login.php
$usuario = requirir_login($config['sesion']['duracion']);
non_gardar_en_cache();

$funeraria = $config['funeraria'];

// Mensaxe tras unha acción (?msg=...). 
// Só aceptamos valores coñecidos:
// nunca se amosa na páxina o texto que chega na URL.
$mensaxes = [
    'gardada'    => 'A necrolóxica gardouse correctamente.',
    'eliminada'  => 'A necrolóxica eliminouse.',
    'non_existe' => 'A necrolóxica non existe (pode que outra persoa a eliminase).',
];
$mensaxe = $mensaxes[$_GET['msg'] ?? ''] ?? null;

// Se a base de datos falla, o detalle queda no rexistro de
// erros de PHP pero o usuario ve un aviso xenérico.
$esquelas = null;
try {
    $esquelas = listar_esquelas(conectar_bd($config['bd']));
} catch (PDOException $ex) {
    error_log('EsquelasWeb (admin/index): ' . $ex->getMessage());
}

$titulo = 'Xestión de necrolóxicas';
require __DIR__ . '/../../include/cabeceira_admin.php';
?>
        <div class="titulo-seccion">
            <h2>Necrolóxicas</h2>
            <a class="boton" href="/admin/editar.php">Nova necrolóxica</a>
        </div>

        <?php if ($mensaxe !== null): ?>
            <p class="aviso"><?= e($mensaxe) ?></p>
        <?php endif; ?>

        <?php if ($esquelas === null): ?>
            <p class="aviso erro">Non foi posible consultar as necrolóxicas. Ténteo de novo máis tarde.</p>
        <?php elseif (count($esquelas) === 0): ?>
            <p>Aínda non hai ningunha necrolóxica.</p>
        <?php else: ?>
            <table class="listaxe">
                <thead>
                    <tr>
                        <th>Funeral</th>
                        <th>Nome</th>
                        <th>Lugar do funeral</th>
                        <th>Estado</th>
                        <th>Accións</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($esquelas as $n): ?>
                    <!-- As pasadas lévan a clase "pasada" (en gris) -->
                    <tr<?= $n['vixente'] ? '' : ' class="pasada"' ?>>
                        <td><?= data_curta($n['data_funeral']) ?> <?= hora_curta($n['hora_funeral']) ?></td>
                        <td>
                            <a href="/admin/ver.php?id=<?= (int) $n['id'] ?>"><?= e($n['nome_completo']) ?></a>
                            <?php if ($n['alcume']): ?>(<?= e($n['alcume']) ?>)<?php endif; ?>
                        </td>
                        <td><?= e($n['lugar_funeral']) ?></td>
                        <td><?= $n['vixente'] ? 'Vixente' : 'Pasada' ?></td>
                        <td class="accions">
                            <a href="/admin/ver.php?id=<?= (int) $n['id'] ?>">Ver</a>
                            <a href="/admin/editar.php?id=<?= (int) $n['id'] ?>">Modificar</a>
                            <a href="/admin/eliminar.php?id=<?= (int) $n['id'] ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>