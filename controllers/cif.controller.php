
<?php
// /controllers/cif.controller.php

session_start();
require_once '../config/db.php';
require_once '../models/cif.model.php';
require_once '../core/session.php';

if (!usuarioAutenticado()) {
    header("Location: /amt_enci/views/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tasa_predeterminada = filter_input(INPUT_POST, 'tasa_predeterminada', FILTER_VALIDATE_FLOAT);
    $unidades = filter_input(INPUT_POST, 'unidades_producidas', FILTER_VALIDATE_INT);
    $producto_id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
    $empresa_id = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);
    $usuario_id = $_SESSION['usuario']['id'];

    if ($tasa_predeterminada && $unidades && $producto_id && $empresa_id) {
        $cifModel = new CifModel($pdo);
        
        $cif_total_aplicado = $tasa_predeterminada * $unidades;

        $datos = [
            'tasa' => $tasa_predeterminada,
            'unidades' => $unidades,
            'total' => $cif_total_aplicado,
            'pro_id' => $producto_id, // Corregido
            'emp_id' => $empresa_id,
            'usuario_id' => $usuario_id
        ];

        if ($cifModel->crear($datos)) {
            $_SESSION['mensaje'] = "CIF aplicado generado correctamente.";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al generar el CIF aplicado.";
            $_SESSION['mensaje_tipo'] = "danger";
        }
    } else {
        $_SESSION['mensaje'] = "Datos incompletos o inválidos. Asegúrate de calcular primero la tasa predeterminada.";
        $_SESSION['mensaje_tipo'] = "warning";
    }

    header("Location: ../views/general/dashboard.php?vista=costos_indirectos&empresa_id=" . $empresa_id);
    exit;
}
?>