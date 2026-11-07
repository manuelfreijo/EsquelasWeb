<?php
// =====================================================================
// sesion.php  Sesións da parte de administración
//
// As opcións de seguridade da cookie (HttpOnly, Secure, SameSite=Strict)
// están en /etc/php.d/99-seguridade.ini, xa definidas
// A cookie SameSite=Strict non se envía en peticións que veñan doutros
// sitios web: é a protección fronte a CSRF desta versión.
// =====================================================================

/**
 * Comproba se hai sesión e inicia.
 */
function iniciar_sesion(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('esquelas_sesion');
        session_start();
    }
}

/**
 * Garda na sesión o usuario.
 * session_regenerate_id(true) cambia o identificador de sesión ó
 * entrar: un identificador obtido antes do login xa non é válido.
 */
function rexistrar_acceso(string $usuario): void
{
    iniciar_sesion();
    session_regenerate_id(true);
    $_SESSION['usuario']           = $usuario;
    $_SESSION['ultima_actividade'] = time();
}

/**
 * Devolve o usuario da sesión, null se non hai sesión.
 * Se pasou máis tempo do permitido sen actividade, péchase a sesión.
 */
function usuario_actual(int $duracion): ?string
{
    iniciar_sesion();

    if (empty($_SESSION['usuario'])) {
        return null;
    }
    if (time() - $_SESSION['ultima_actividade'] > $duracion) {
        pechar_sesion();
        return null;
    }

    $_SESSION['ultima_actividade'] = time();   // renovar o tempo
    return $_SESSION['usuario'];
}

/**
 * En cada páxina de /admin: vreificase a sesión
 * e e non ser válida remite a login.
 */
function requirir_login(int $duracion): string
{
    non_gardar_en_cache();

    $usuario = usuario_actual($duracion);
    if ($usuario === null) {
        header('Location: /admin/login.php');
        exit;
    }
    return $usuario;
}

/**
 * Pecha a sesión: borra os datos, a cookie e mais a sesión do servidor.
 */
function pechar_sesion(): void
{
    iniciar_sesion();
    $_SESSION = [];
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_destroy();
}

/**
 * Indica ao navegador que non garde cachñe das páxinas de administración
 * Así despóis de saír, o botón "Atrás" non amosa contido dunha sesión pechada.
 */
function non_gardar_en_cache(): void
{
    header('Cache-Control: no-store');
}

/**
 * IP do cliente. Hay que configurar o proxy Apache
 * para que remita a IP real do visitante.
 */
function ip_cliente(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '';
}