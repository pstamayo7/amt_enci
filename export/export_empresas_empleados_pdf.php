<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/empleado.model.php';

use Dompdf\Dompdf;

session_start();
$usuario_id = $_SESSION['usuario']['id'] ?? null;

if (!$usuario_id) {
    die("No autorizado");
}

$empleadoModel = new Empleado($pdo);
$empleados = $empleadoModel->listarPorUsuario($usuario_id);

// Agrupar empleados por empresa
$agrupados = [];
foreach ($empleados as $empl) {
    if ($empl['empl_estado'] === 'activo' && $empl['emp_nombre']) {
        $agrupados[$empl['emp_nombre']][] = $empl;
    }
}

// HTML
$html = '<h2 style="text-align:center;">Empresas y sus Empleados</h2>';
foreach ($agrupados as $empresa => $lista) {
    $html .= "<h4>📦 {$empresa}</h4><ul>";
    foreach ($lista as $empl) {
        $html .= "<li>{$empl['empl_nombre']} ({$empl['empl_cedula']})</li>";
    }
    $html .= '</ul><hr>';
}

// Generar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("empresas_empleados.pdf", ["Attachment" => false]);
exit;
