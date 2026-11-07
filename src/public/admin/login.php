<?php
// =====================================================================
// admin/login.php - Formulario de acceso para usuarios
//
// GET:  amosa o formulario.
// POST: valida o usuario contra o LDAP e crea a sesión.
// =====================================================================

declare(strict_types=1);

$config = require __DIR__ . '/../../config/config.php';
require __DIR__ . '/../../include/utilidades.php';
require __DIR__ . '/../../include/ldap.php';
require __DIR__ . '/../../include/sesion.php';

non_gardar_en_cache();

// Se xa ten unha sesión válida, vai á xestión
if (usuario_actual($config['sesion']['duracion']) !== null) {
    header('Location: /admin/');
    exit;
}

$usuario = '';
$mensaxe = isset($_GET['saida']) ? 'Pechouse a sesión.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Minúsculas e sen espazos: o mesmo usuario sempre co mesmo nome
    $usuario     = strtolower(trim((string) ($_POST['usuario'] ?? '')));
    $contrasinal = (string) ($_POST['contrasinal'] ?? '');

    $resultado = autenticar_editor($config['ldap'], $usuario, $contrasinal);

    // Rexistro de todos os intentos de login. Só se escribe o nome se é válido.
    error_log(sprintf('EsquelasWeb: acceso %s usuario=%s ip=%s',
        $resultado,
        usuario_valido($usuario) ? $usuario : '(non válido)',
        ip_cliente()));

    if ($resultado === 'ok') {
        rexistrar_acceso($usuario);
        header('Location: /admin/');
        exit;
    }

    // Espera 1 segundo tras un intento fallido
    sleep(1);

    $mensaxe = $resultado === 'erro_servizo'
        ? 'O servizo de autenticación non está dispoñible. Ténteo máis tarde.'
        : 'Usuario ou contrasinal incorrectos.';
}

$funeraria = $config['funeraria'];
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="utf-8">
    <title>Acceso - <?= e($funeraria['nome']) ?></title>
    <link rel="stylesheet" href="/css/estilo.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <header class="cabeceira">
        <h1><?= e($funeraria['nome']) ?></h1>
    </header>

    <main class="admin">
        <form class="caixa-acceso" method="post" action="/admin/login.php">
            <h2>Acceso para o persoal</h2>

            <?php if ($mensaxe): ?>
                <p class="mensaxe-acceso"><?= e($mensaxe) ?></p>
            <?php endif; ?>

            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" value="<?= e($usuario) ?>"
                   autocomplete="username" required autofocus>

            <label for="contrasinal">Contrasinal</label>
            <input type="password" id="contrasinal" name="contrasinal"
                   autocomplete="current-password" required>

            <button type="submit">Entrar</button>

            <p class="volver"><a href="/">Volver á páxina pública</a></p>
        </form>
    </main>
</body>
</html>