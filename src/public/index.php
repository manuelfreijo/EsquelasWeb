<?php
// =====================================================================
// index.php - Parte pública: Necrolóxicas dos servizos vixentes
//
// Amosa as necrolóxicas cuxa data de funeral é igual ou posterior á
// data actual. O filtro faino MariaDB con CURDATE().
// =====================================================================

declare(strict_types=1);

// A configuración e as funcións están FÓRA de public/: non son 
// accesibles desde o navegador.
$config = require __DIR__ . '/../config/config.php';
require __DIR__ . '/../include/bd.php';
require __DIR__ . '/../include/utilidades.php';

$esquelas = [];
$erro     = false;

try {
    $pdo = conectar_bd($config['bd']);

    // Consulta fixa, sen datos do usuario: para evitar risco de inserción.
    $sql = 'SELECT nome_completo, alcume, idade, localidade, data_falecemento,
                   lugar_velorio, data_funeral, hora_funeral, lugar_funeral,
                   lugar_enterro, familiares, mensaxe
              FROM esquelas
             WHERE data_funeral >= CURDATE()
             ORDER BY data_funeral, hora_funeral';

    $esquelas = $pdo->query($sql)->fetchAll();
} catch (PDOException $ex) {
    // O detalle do erro vai ao rexistro do servidor, non se amosa.
    error_log('EsquelasWeb: erro de base de datos: ' . $ex->getMessage());
    $erro = true;
}

$funeraria = $config['funeraria'];

// Para o enlace tel: só se deixan os díxitos e o signo +
$telefono_enlace = preg_replace('/[^0-9+]/', '', $funeraria['telefono']);
?>
<!DOCTYPE html>
<html lang="gl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Necrolóxicas - <?= e($funeraria['nome']) ?></title>
    <link rel="stylesheet" href="/css/estilo.css">
</head>
<body>
    <header class="cabeceira">
        <h1><?= e($funeraria['nome']) ?></h1>
    </header>

    <!-- No HTML vai primeiro o contido principal e despois o contacto:
         en móbil aparecen nesa orde; no resto o CSS sitúa o
         contacto na columna esquerda. -->
    <div class="contido">
        <main>
            <h2 class="titulo-seccion">Necrolóxicas de servizos funerarios</h2>

            <?php if ($erro): ?>
                <p class="aviso">Neste momento non é posible amosar as necrolóxicas.
                   Por favor, ténteo de novo máis tarde.</p>

            <?php elseif (count($esquelas) === 0): ?>
                <p class="aviso">Non hai servizos programados.</p>

            <?php else: ?>
                <?php foreach ($esquelas as $n): ?>
                    <!-- Modificado para sacar ó código fora e poder reutilizalo -->
                    <?php require __DIR__ . '/../include/tarxeta_esquela.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>

        <aside class="contacto">
            <h2>Contacto</h2>
            <p><?= el($funeraria['enderezo']) ?></p>
            <p><a href="tel:<?= e($telefono_enlace) ?>">Tel. <?= e($funeraria['telefono']) ?></a></p>
            <p class="acceso"><a href="/admin/">Acceso para o persoal</a></p>
        </aside>
    </div>
</body>
</html>