<?php
session_start();

// Opcional: solo administradores pueden registrar usuarios
// if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "admin") {
//     header("Location: login.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de Registro - Administrador</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/lab/css/style.css">
</head>

<body class="bg-light">

    <?php include "sidebar.php"; ?>

    <div class="container mt-5" style="max-width: 900px;">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">👤 Registrar Nuevo Usuario</h4>
            </div>

            <div class="card-body">

                <form action="guardar_usuario.php" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombreCompleto" class="form-control" required>
                        </div>
                   
                        <div class="col-md-6">
                            <label class="form-label">Nombre de usuario</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Rol del usuario</label>
                            <select name="rol" class="form-control" required>
                                <option value="">Selecciona un rol</option>
                                <option value="admin">Administrador</option>
                                <option value="recepcion">Recepción</option>
                                <option value="lab">Laboratorio</option>
                                <option value="caja">Caja</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="password2" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success px-4">Registrar Usuario</button>
                    <a href="../bienvenida.php" class="btn btn-secondary">Cancelar</a>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById("toggleSidebar").addEventListener("click", function() {
        let sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("hidden");
    });
    </script>

</body>

</html>