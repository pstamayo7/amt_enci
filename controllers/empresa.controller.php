<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../models/empresa.model.php';
require_once __DIR__ . '/../core/session.php';

$empresaModel = new Empresa($pdo);

// Crear empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_empresa'])) {
    $nombre = $_POST['nombre'];
    $ruc = $_POST['ruc'];
    $actividad = $_POST['actividad'];
    $usuario_id = $_SESSION['usuario']['id'];

    $empresaModel->crear($nombre, $ruc, $actividad, $usuario_id);
    header("Location: ../views/general/empresa/listar.php");
    exit;
}

// Eliminar lógicamente
if (isset($_GET['eliminar'])) {
    $emp_id = $_GET['eliminar'];
    $usuario_id = $_SESSION['usuario']['id'];
    $empresaModel->eliminarLogicamente($emp_id, $usuario_id);
    header("Location: ../views/general/empresa/listar.php");
    exit;
}
