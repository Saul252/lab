<?php
session_start();
if ($_SESSION["rol"] !== "admin") {
    die("No tienes permisos para editar usuarios.");
}

require "../conexion.php";


$sql_listar = "SELECT * FROM usuarios";
$result= $conexion->query($sql_listar);


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="/lab/css/sidebars.css" rel="stylesheet">
</head>

<body>
   <?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'Usuarios'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>
    <div class="container mt-5">
        <h4>Bienvenido, <strong><?php echo $_SESSION['rol']; ?></strong></h4>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Usuarios registrados</h2>
            <a href="registrar.php" class="btn btn-primary">➕ Crear nuevo usuario</a>
        </div>

       <div class="table-responsive scroll-tabla" >
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark sticky-top">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th style="width: 160px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id_usuario']; ?></td>
                <td><?= $row['nombre']; ?></td>
                <td><?= $row['usuario']; ?></td>
                <td><?= $row['rol']; ?></td>
                <td>
    <div class="btn-group" role="group">
        <a href="editar_usuario.php?id=<?= $row['id_usuario']; ?>" class="btn btn-warning btn-sm">
            ✏ Editar
        </a>

        <a href="acciones_usuarios/eliminar_usuario.php?id=<?= $row['id_usuario']; ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
            🗑 Eliminar
        </a>
    </div>
</td>

            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>


    </div>
    <!-- Modal de Éxito (oculto por defecto) -->
    <div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="modalExitoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalExitoLabel">Éxito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Usuario eliminado con éxito.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para mostrar modal y recargar -->

</body>

</html>