<?php
// /views/general/ajax/get_productos.php

// Iniciar sesión para acceder a $_SESSION
session_start(); 

// Incluir los archivos necesarios
require_once '../../../config/db.php';
require_once '../../../models/producto.model.php';
require_once '../../../core/session.php'; // Para la función usuarioAutenticado()

header('Content-Type: application/json');

// 1. Seguridad: Verificar que el usuario esté autenticado
if (!usuarioAutenticado()) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

// 2. Obtener el ID de la empresa desde la URL
$empresa_id = filter_input(INPUT_GET, 'empresa_id', FILTER_VALIDATE_INT);

if (!$empresa_id) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'ID de empresa no proporcionado']);
    exit;
}

// 3. Lógica para obtener productos (imitando a productos.php)
try {
    // Creamos una instancia del modelo de producto, tal como lo hace tu vista
    $productoModel = new Producto($pdo); 
    
    // Llamamos al método que ya sabemos que funciona
    $productos = $productoModel->listarPorEmpresa($empresa_id);

    // Devolvemos los productos en formato JSON
    echo json_encode($productos);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    // No muestres el error real en producción por seguridad
    echo json_encode(['error' => 'Error al consultar la base de datos.']);
    exit;
}
?>