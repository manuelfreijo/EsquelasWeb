<?php
// =====================================================================
// tarxeta_esquela.php  Bloque HTML dunha necrolóxica (tarxeta)
//
// Non é unha función se non un anaco de plantilla que se inclúe con
// require onde se queira amosar unha necrolóxica. Espera atopar a
// variable $n cos datos da necrolóxica
//
// =====================================================================
?>
<article class="esquela">
    <h3>
        <?= e($n['nome_completo']) ?>
        <?php if ($n['alcume']): ?>
            <span class="alcume">(<?= e($n['alcume']) ?>)</span>
        <?php endif; ?>
    </h3>

    <p class="datos">
        Faleceu o <?= data_galego($n['data_falecemento']) ?><?php if ($n['idade'] !== null): ?>, aos <?= e((string) $n['idade']) ?> anos<?php endif; ?>.
        <?php if ($n['localidade']): ?><br>Localidade: <?= e($n['localidade']) ?><?php endif; ?>
    </p>

    <?php if ($n['familiares']): ?>
        <p class="familiares"><?= el($n['familiares']) ?></p>
    <?php endif; ?>

    <dl class="servizos">
        <?php if ($n['lugar_velorio']): ?>
            <dt>Velorio</dt>
            <dd><?= e($n['lugar_velorio']) ?></dd>
        <?php endif; ?>

        <dt>Funeral</dt>
        <dd><?= data_galego($n['data_funeral']) ?>, ás <?= hora_curta($n['hora_funeral']) ?> h,
            en <?= e($n['lugar_funeral']) ?></dd>

        <?php if ($n['lugar_enterro']): ?>
            <dt>Enterro</dt>
            <dd><?= e($n['lugar_enterro']) ?></dd>
        <?php endif; ?>
    </dl>

    <?php if ($n['mensaxe']): ?>
        <p class="mensaxe"><?= el($n['mensaxe']) ?></p>
    <?php endif; ?>
</article>