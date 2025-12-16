<?php
session_start();
if ($_SESSION["rol"] !== "admin") {
    die("No tienes permisos");
}

require "../conexion.php";


$id = $_GET["id"];

// Obtener datos del usuario
$query = "SELECT * FROM usuarios WHERE id_usuario = $id LIMIT 1";
$result = $conexion->query($query);

if ($result->num_rows === 0) {
    die("Usuario no encontrado.");
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">✏ Editar Usuario</h4>
        </div>

        <div class="card-body">

            <form action="acciones_usuarios/actualizar_usuario.php" method="POST">

                <input type="hidden" name="id_usuario" value="<?= $data['id_usuario']; ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombreCompleto" class="form-control"
                               value="<?= $data['nombre']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" name="usuario" class="form-control"
                               value="<?= $data['usuario']; ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rol del usuario</label>
                    <select name="rol" class="form-control" required>
                        <option value="admin"      <?= $data['rol']=='admin'?'selected':'' ?>>Administrador</option>
                        <option value="recepcion"  <?= $data['rol']=='recepcion'?'selected':'' ?>>Recepción</option>
                        <option value="lab"        <?= $data['rol']=='lab'?'selected':'' ?>>Laboratorio</option>
                        <option value="caja"       <?= $data['rol']=='caja'?'selected':'' ?>>Caja</option>
                    </select>
                </div>

                <hr>

                <h5 class="text-muted">Cambiar contraseña (opcional)</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Repetir contraseña</label>
                        <input type="password" name="password2" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4">Guardar cambios</button>
                <button class="btn btn-secondary" onclick="history.back()">
    ⬅ Volver
</button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
