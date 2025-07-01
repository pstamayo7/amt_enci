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
    // Datos del formulario de la barra lateral
    $datos = [
        'fecha' => $_POST['fecha_aplicada'],
        'unidades' => $_POST['unidades_producidas'],
        'pro_id' => $_POST['producto_id'],
        'emp_id' => $_POST['empresa_id'],
        'usr_id' => $_SESSION['usuario']['id'],
        
        // Datos ocultos del formulario principal (la "foto")
        'tasa' => $_POST['tasa_predeterminada'],
        'base_anual' => $_POST['produccion_anual'],
        'mat_ind' => $_POST['materiales_indirectos'],
        'mo_ind' => $_POST['mano_obra_indirecta'],
        'depreciacion' => $_POST['depreciacion'],
        'seguros' => $_POST['seguros'],
        'combustibles' => $_POST['combustibles'],
        'serv_basicos' => $_POST['servicios_basicos'],
        'arriendo' => $_POST['arriendo'],
        'otros_cif' => $_POST['otros_cif']
    ];

    // Calcular el total aplicado
    $datos['total_aplicado'] = $datos['tasa'] * $datos['base_anual'];

    $cifModel = new CifModel($pdo);
    if ($cifModel->crear($datos)) {
        $_SESSION['mensaje'] = "CIF aplicado y todos sus detalles fueron guardados correctamente.";
    } else {
        $_SESSION['mensaje'] = "Error al guardar el CIF aplicado.";
    }

    header("Location: ../views/general/dashboard.php?vista=costos_indirectos&empresa_id=" . $datos['emp_id']);
    exit;
}
?>