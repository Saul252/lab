<?php
session_start();
require "conexion.php";


$sql_listar = "SELECT * FROM usuarios";
$result= $conexion->query($sql_listar);


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">Menu</button>

            <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
                aria-labelledby="offcanvasWithBothOptionsLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">Backdrop with scrolling</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <p>Try scrolling the rest of the page to see this option in action.</p>
                </div>
            </div>
            <span class="navbar-brand">Administracion de Usuarios</span>

            <a href="logout.php" class="btn btn-light">Cerrar sesión</a>
        </div>
    </nav>
<div class="container mt-5">
<h4>Bienvenido, <strong><?php echo $_SESSION['rol']; ?></strong></h4>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Usuarios registrados</h2>
    <a href="usuarios/registrar.php" class="btn btn-primary">➕ Crear nuevo usuario</a>
</div>

<table class="table table-bordered table-hover">
    <thead class="table-dark">
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
                    <a href="usuarios/editar_usuario.php?id=<?= $row['id_usuario']; ?>" class="btn btn-warning btn-sm">
                        ✏ Editar
                    </a>
                    <a href="usuarios/eliminar_usuario.php?id=<?= $row['id_usuario']; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
                       🗑 Eliminar
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
 
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