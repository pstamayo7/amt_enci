<?php
require_once __DIR__ . '/../models/empresa.model.php';
include_once '/../models/empleado.model.php';
include_once '/../models/parametro.model.php';

$usuario_id = $_SESSION['usuario']['id']; // Asegúrate de que la sesión esté activa
$empresa_modelo = new Empresa($pdo);
$empleado_modelo = new Empleado($pdo);
$parametros_modelo = new Parametros($pdo);

$empresas1 = $empresa_modelo->listarPorUsuario($usuario_id);

// Verificar si hay empresa seleccionada por GET
$filtroEmpresaSeleccionado = $_GET['empresa_id'] ?? null;

$empleados = [];
if ($filtroEmpresaSeleccionado) {
    $empleados = $empleado_modelo->listarPorEmpresa($filtroEmpresaSeleccionado);
}

$parametros = $parametros_modelo->obtenerTodosAsociativo();

require_once __DIR__ .'views/general/partes/roles_pago/formulario_rol_pago.php';
