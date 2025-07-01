<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loginUsuario($usuario) {
    $_SESSION['usuario'] = [
        'id' => $usuario['usu_id'],
        'nombre' => $usuario['usu_nombre'],
        'rol' => $usuario['usu_rol'],
        'correo' => $usuario['usu_correo']
    ];
}

function logoutUsuario() {
    session_destroy();
}

function usuarioAutenticado() {
    return isset($_SESSION['usuario']);
}
