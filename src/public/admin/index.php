<?php
// =====================================================================
// admin/index.php - Inicio da parte de administración
//
// Dede aqui faise a xestión das necrolóxicas.
// =====================================================================

declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
require __DIR__ . '/../../include/utilidades.php';
require __DIR__ . '/../../include/sesion.php';

// Sen sesión válida hay que envía a login.php
$usuario = requirir_login($config['sesion']['duracion']);

$funeraria = $config['funeraria'];
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="utf-8">
    <title>Xestión de necrolóxicas - <?= e($funeraria['nome']) ?></title>
    <link rel="stylesheet" href="/css/estilo.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <header class="cabeceira">
        <h1><?= e($funeraria['nome']) ?></h1>
    </header>

    <div class="barra-admin">
        <span>Xestión de necrolóxicas · Seión do usuario <strong><?= e($usuario) ?></strong></span>
        <form method="post" action="/admin/sair.php">
            <button type="submit">Saír</button>
        </form>
    </div>

    <main class="admin">
        <p>Aquí vai a xestión das necrolóxicas (crear, modificar e eliminar)</p>
    </main>
</body>
</html>