<?php
// =====================================================================
// admin/sair.php  Pechar a sesión
//
// Só acepta POST (o botón "Saír"): Para que una redirección ou
// recarga do navegador non peche a sesión.
// =====================================================================

declare(strict_types=1);

require __DIR__ . '/../../include/sesion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    iniciar_sesion();
    if (!empty($_SESSION['usuario'])) {
        error_log(sprintf('EsquelasWeb: saída usuario=%s ip=%s', $_SESSION['usuario'], ip_cliente()));
    }
    pechar_sesion();
}

header('Location: /admin/login.php?saida=1');
exit;