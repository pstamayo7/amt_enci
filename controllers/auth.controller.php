<?php
require_once __DIR__ . '/../models/usuario.model.php';
require_once __DIR__ . '/../core/session.php';

$usuarioModel = new Usuario($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($nombre) || empty($correo) || empty($contrasena)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        $usuario = new Usuario($pdo);

        if ($usuario->existeCorreo($correo)) {
            $error = "Este correo ya está registrado.";
        } else {
            $registroExitoso = $usuario->registrar($nombre, $correo, $contrasena);
            if ($registroExitoso) {
                $mensaje = "Registro exitoso. Espera la aprobación del administrador.";
            } else {
                $error = "Hubo un error al registrar el usuario.";
            }
        }
    }
}

// LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($correo) || empty($contrasena)) {
        $error = "Correo y contraseña obligatorios.";
    } else {
        $usuario = $usuarioModel->login($correo, $contrasena);
        if ($usuario) {
            loginUsuario($usuario);
            // Redirigir según el rol del usuario
            switch ($usuario['usu_rol']) {
                case 'admin':
                    header('Location: ../admin/dashboard.php');
                    break;
                case 'docente':
                    header('Location: ../general/dashboard.php'); // dashboard general para ambos
                    break;
                case 'estudiante':
                    header('Location: ../general/dashboard.php'); // dashboard general para ambos
                    break;
                default:
                    // Si llega aquí, es un rol no reconocido
                    $error = "Rol de usuario no válido.";
                    exit;
            }
            exit;

            exit;
        } else {
            $error = "Credenciales incorrectas o usuario no aprobado.";
        }
    }
}
