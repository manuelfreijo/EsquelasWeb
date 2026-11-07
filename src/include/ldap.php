<?php
// =====================================================================
// ldap.php - Autenticación dos editores contra OpenLDAP
//
// PHP non garda nin comproba contrasinais: pregúntalle ó LDAP se son
// correctos (ldap_bind) e comproba se o usuario pertence ó grupo.
// =====================================================================

/**
 * Comproba que o nome de usuario só ten caracteres permitidos:
 * minúsculas, díxitos, punto, guión e guión baixo (máximo 50, o tamaño
 * da columna usuario da táboa auditoria).
 * Rexeitar o resto para evitar sorpresas no LDAP e nos logs.
 */
function usuario_valido(string $usuario): bool
{
    return preg_match('/^[a-z0-9._-]{1,50}$/', $usuario) === 1;
}

/**
 * Autenticar. Devolve un destes resultados:
 *   'ok'           contrasinal correcto e usuario do grupo de editores
 *   'credenciais'  usuario ou contrasinal incorrectos
 *   'non_editor'   contrasinal correcto, pero non pertence a editores
 *   'erro_servizo' non se puido contactar co LDAP
 *
 * O usuario debe chegar xa en minúsculas: o LDAP compara o uid sen
 * distinguir maiúsculas, pero memberUid si as distingue.
 */
function autenticar_editor(array $c, string $usuario, string $contrasinal): string
{
    // Un contrasinal baleiro o LDAP interpretaríao como acceso anónimo
    // Por iso hai que rexeitalo sempre.
    if (!usuario_valido($usuario) || $contrasinal === '') {
        return 'credenciais';
    }

    $con = ldap_connect($c['uri']);
    if ($con === false) {
        return 'erro_servizo';
    }
    ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($con, LDAP_OPT_NETWORK_TIMEOUT, 5);
    ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

    // 1. Validar o contrasinal: conectar ao LDAP como ese usuario.
    //    ldap_escape() impide que caracteres especiais alteren o DN.
    $dn = 'uid=' . ldap_escape($usuario, '', LDAP_ESCAPE_DN) . ',' . $c['base_usuarios'];

    if (!@ldap_bind($con, $dn, $contrasinal)) {
        $erro = ldap_errno($con);
        ldap_unbind($con);
        // 49 = credenciais incorrectas
        if ($erro !== 49) {
            error_log('EsquelasWeb: erro LDAP ' . $erro . ': ' . ldap_err2str($erro));
            // calquera outro é un problema do servizo
            return 'erro_servizo';
        }
        return 'credenciais';
    }

    // 2. Comprobar que está no grupo de editores (atributo memberUid).
    $filtro = sprintf('(&(objectClass=posixGroup)(cn=%s)(memberUid=%s))',
        ldap_escape($c['grupo_editores'], '', LDAP_ESCAPE_FILTER),
        ldap_escape($usuario, '', LDAP_ESCAPE_FILTER));

    $res = @ldap_search($con, $c['base_grupos'], $filtro, ['cn']);
    if ($res === false) {
        error_log('EsquelasWeb: erro na busca LDAP: ' . ldap_error($con));
        ldap_unbind($con);
        return 'erro_servizo';
    }

    $atopados = ldap_count_entries($con, $res);
    ldap_unbind($con);

    return $atopados === 1 ? 'ok' : 'non_editor';
}