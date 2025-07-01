<?php
require_once __DIR__ . '/../models/usuario.model.php';
require_once __DIR__ . '/../core/session.php';

// Verifica que el usuario sea admin
if (!usuarioAutenticado() || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$usuarioModel = new Usuario($pdo);

// Procesar actualización de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_usuario'])) {
    $id = $_POST['usu_id'];
    $estado = $_POST['usu_estado'];
    $rol = $_POST['usu_rol'];

    $usuarioModel->actualizarEstadoYRol($id, $estado, $rol);

    // Notificación temporal de éxito
    $_SESSION['guardado'] = true;

    // Redirección limpia para evitar reenvío
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$estado = $_GET['estado'] ?? null;
$usuarios = $usuarioModel->obtenerUsuariosPorEstado($estado);
