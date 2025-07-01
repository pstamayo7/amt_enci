<style>
/* Estructura contenedor general */
.container {
    display: flex;
    min-height: 100vh;
}

/* Aside lateral */
aside {
    width: 220px;
    background-color: #2c3e50;
    color: #ecf0f1;
    padding: 20px 15px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    height: 100vh;
    box-sizing: border-box;
}

aside h2 {
    font-size: 1.3rem;
    margin-bottom: 20px;
    border-bottom: 1px solid #7f8c8d;
    padding-bottom: 10px;
}

aside ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

aside ul li {
    margin-bottom: 15px;
}

aside ul li a {
    color: #ecf0f1;
    text-decoration: none;
    font-weight: 500;
    display: block;
    transition: background-color 0.2s;
    padding: 8px;
    border-radius: 5px;
}

aside ul li a:hover {
    background-color: #34495e;
}

/* Contenido principal */
main {
    flex: 1;
    padding: 30px;
    background-color: #f4f6f8;
    box-sizing: border-box;
    overflow-y: auto;
}

</style>

    <div class="container">

        <!-- ASIDE LATERAL PARA EL MÓDULO DE ROLES DE PAGO -->
        <aside style="position: fixed; left: 0; top: 60px; height: calc(100vh - 60px); background:rgb(6, 115, 108); padding: 1rem; width: 220px; border-right: 1px solid #ccc;">
            <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Roles de Pago</h2>
            <ul style="list-style: none; padding: 0;">
                <li><a href="dashboard.php?vista=mano_de_obra&view=formulario_rol_pago">➕ Generar Rol de Pago</a></li>
                <li><a href="dashboard.php?vista=mano_de_obra&view=ver_roles_pago">📄 Ver Roles por Empresa</a></li>
                 <li><a href="dashboard.php?vista=mano_de_obra&view=tarjetas_de_tiempo">📄 Ver Roles por Empresa</a></li>
            </ul>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main style="margin-left: 240px; padding: 2rem;">
            <?php
            if (isset($_GET['view']) && $_GET['view'] === 'formulario_rol_pago') {
                include __DIR__ . '/roles_pago/formulario_rol_pago.php';
            } elseif (isset($_GET['view']) && $_GET['view'] === 'ver_roles_pago') {
                include __DIR__ . '/roles_pago/ver_roles_pago.php';
                 } elseif (isset($_GET['view']) && $_GET['view'] === 'tarjetas_de_tiempo') {
                include __DIR__ . '/roles_pago/tarjetas_de_tiempo.php';
            } else {
                echo "<h3>Bienvenido al módulo de Roles de Pago</h3>
                      <p>Seleccione una opción en el menú lateral.</p>";
            }
            ?>
        </main>

    </div>

