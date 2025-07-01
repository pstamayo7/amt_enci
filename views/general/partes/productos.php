<?php
require_once __DIR__ . '/../../../models/empresa.model.php';
require_once __DIR__ . '/../../../models/producto.model.php';

$empresaModel = new Empresa($pdo);
$productoModel = new Producto($pdo);

$usuario_id = $_SESSION['usuario']['id'];

// Obtener empresas activas del usuario
$empresas = $empresaModel->listarPorUsuario($usuario_id);

// Selección de empresa actual
$empresa_id = $_GET['empresa_id'] ?? ($empresas[0]['emp_id'] ?? null);

// Crear producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_producto'])) {
    $productoModel->crear(
        $_POST['pro_nombre'],
        $_POST['pro_codigo'],
        $_POST['pro_unidad_medida'],
        $_POST['empresa_id']
    );
    header("Location: dashboard.php?vista=productos&empresa_id=" . $_POST['empresa_id']);
    exit;
}

// Eliminar producto
if (isset($_GET['eliminar_producto'])) {
    $productoModel->eliminarLogicamente($_GET['eliminar_producto'], $empresa_id);
    header("Location: dashboard.php?vista=productos&empresa_id=" . $empresa_id);
    exit;
}

// Productos por empresa seleccionada
$limite = 5;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

$productos_totales = $empresa_id ? $productoModel->listarPorEmpresa($empresa_id) : [];
$productos = array_slice($productos_totales, $offset, $limite);
$total_paginas = ceil(count($productos_totales) / $limite);

?>

<style>
    .panel-productos {
        max-width: 900px;
        margin: 0 auto;
        background: #ffffff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    h3 {
        margin-top: 0;
        color: #2e7d32;
    }

    select,
    input,
    button {
        padding: 10px;
        margin-bottom: 10px;
        width: 100%;
        font-size: 1em;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 0.95em;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #e0f2f1;
    }

    .btn-danger {
        background-color: #d32f2f;
        color: white;
        border: none;
        padding: 8px 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-danger:hover {
        background-color: #b71c1c;
    }

    .btn-green {
        background-color: #388e3c;
        color: white;
        border: none;
        padding: 8px 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-green:hover {
        background-color: #2e7d32;
    }
</style>

<div class="panel-productos">
    <h3>Gestión de Productos</h3>

    <?php if (count($empresas) > 0): ?>
        <!-- Selector de empresa -->
        <form method="GET" style="margin-bottom: 15px;">
            <input type="hidden" name="vista" value="productos">
            <label for="empresa_id"><strong>Selecciona una empresa:</strong></label>
            <select name="empresa_id" id="empresa_id" onchange="this.form.submit()" required>
                <option value="">-- Elegir empresa --</option>
                <?php foreach ($empresas as $emp): ?>
                    <option value="<?php echo $emp['emp_id']; ?>" <?php echo ($empresa_id == $emp['emp_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['emp_nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($empresa_id): ?>
            <!-- Formulario creación de producto -->
            <button class="btn-green" onclick="toggleFormProducto()" style="margin-bottom: 10px;">+ Crear Producto</button>

            <div id="formProducto" style="display: none; margin-bottom: 20px; background: #f1f8e9; padding: 15px; border-radius: 8px;">
                <form method="POST">
                    <input type="hidden" name="empresa_id" value="<?php echo $empresa_id; ?>">
                    <input type="text" name="pro_nombre" placeholder="Nombre del producto" required>
                    <input type="text" name="pro_codigo" placeholder="Código del producto" required>
                    <input type="text" name="pro_unidad_medida" placeholder="Unidad de medida (ej: kg, unidad)" required>
                    <button type="submit" name="crear_producto" class="btn-green">Guardar Producto</button>
                </form>
            </div>


            <!-- Tabla de productos -->
            <?php if (count($productos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Unidad</th>
                            <th>Stock</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['pro_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($prod['pro_codigo']); ?></td>
                                <td><?php echo htmlspecialchars($prod['pro_unidad_medida']); ?></td>
                                <td><?php echo number_format($prod['pro_stock_actual'], 2); ?></td>
                                <td>
                                    <a href="dashboard.php?vista=productos&empresa_id=<?php echo $empresa_id; ?>&eliminar_producto=<?php echo $prod['pro_id']; ?>"
                                        onclick="return confirm('¿Eliminar este producto?')">
                                        <button class="btn-danger">Eliminar</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
                <?php if ($total_paginas > 1): ?>
                    <div style="margin-top: 15px; text-align: center;">
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <a href="dashboard.php?vista=productos&empresa_id=<?php echo $empresa_id; ?>&pagina=<?php echo $i; ?>"
                                style="margin: 0 6px; text-decoration: none; font-weight: <?php echo ($i == $pagina) ? 'bold' : 'normal'; ?>;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <p>No hay productos registrados para esta empresa.</p>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <p>No tienes empresas registradas aún. Crea una primero.</p>
    <?php endif; ?>
</div>

<script>
    function toggleFormProducto() {
        const form = document.getElementById('formProducto');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
</script>