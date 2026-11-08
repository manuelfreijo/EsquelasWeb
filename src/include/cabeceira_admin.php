<?php
// =====================================================================
// cabeceira_admin.php  Cabeceira común das páxinas de administración
//
// Anaco de plantilla (como en tarxeta_esquela.php). Espera atopar:
//   $titulo     título da páxina (pestana do navegador)
//   $funeraria  datos da funeraria (config.php)
//   $usuario    usuario da sesión
// Abre <main class="admin">; cada páxina debe péchalo ó final.
// =====================================================================
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="utf-8">
    <title><?= e($titulo) ?> - <?= e($funeraria['nome']) ?></title>
    <link rel="stylesheet" href="/css/estilo.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <header class="cabeceira">
        <h1><?= e($funeraria['nome']) ?></h1>
    </header>

    <div class="barra-admin">
        <span><a href="/admin/">Xestión de necrolóxicas</a> · Sesión iniciada como <strong><?= e($usuario) ?></strong></span>
        <form method="post" action="/admin/sair.php">
            <button type="submit">Saír</button>
        </form>
    </div>

    <main class="admin">