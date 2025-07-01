<?php
require_once __DIR__ . '/../core/session.php';

if (!usuarioAutenticado()) {
    header("Location: /amt_enci/views/auth/login.php");
    exit();
}

$nombre = $_SESSION['usuario']['nombre'];
$rol = $_SESSION['usuario']['rol'];
