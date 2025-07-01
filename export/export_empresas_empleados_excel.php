<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/empleado.model.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Empresas y Empleados");

// Estilos de encabezado
$sheet->setCellValue('A1', 'Empresa');
$sheet->setCellValue('B1', 'Empleado');
$sheet->setCellValue('C1', 'Cédula');

$row = 2;
foreach ($agrupados as $empresa => $lista) {
    foreach ($lista as $empl) {
        $sheet->setCellValue("A{$row}", $empresa);
        $sheet->setCellValue("B{$row}", $empl['empl_nombre']);
        $sheet->setCellValue("C{$row}", $empl['empl_cedula']);
        $row++;
    }
}

// Descargar archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="empresas_empleados.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;