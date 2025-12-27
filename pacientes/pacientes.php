<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

require "../conexion.php";

// =============================
// FILTRO DE BÚSQUEDA
// =============================
$busqueda = "";
$where = "1";

if (isset($_GET["buscar"]) && !empty($_GET["buscar"])) {
    $busqueda = $conexion->real_escape_string($_GET["buscar"]);
    $where = "nombre LIKE '%$busqueda%' 
              OR email LIKE '%$busqueda%' 
              OR telefono LIKE '%$busqueda%'";
}

$sql = "SELECT * FROM pacientes WHERE $where ORDER BY fecha_registro DESC";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pacientes Registrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">
</head>

<body class="bg-light">
 <?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'Pacientes'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>

    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Pacientes Registrados</h2>
            <a href="agregarPaciente.php" class="btn btn-success">➕ Agregar Paciente</a>
        </div>

        <!-- BUSCADOR -->
        <form class="row g-3 mb-4" method="GET">
            <div class="col-md-4">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar nombre, email, teléfono"
                    value="<?= $busqueda ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Buscar</button>
            </div>
            <div class="col-md-2">
                <a href="/lab/pacientes/pacientes.php" class="btn btn-secondary w-100">Limpiar</a>
            </div>
        </form>

        <!-- TABLA -->
       <div class="table-responsive scroll-tabla rounded " style="max-height:60vh; overflow:auto;">

            <table class="table table-bordered table-hover bg-white rounded">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Médico</th>
                        <th>Fecha</th>
                        <th>Opciones</th>
                    </tr>
                </thead>

                <tbody class="rounded">
                    <?php if ($resultado->num_rows > 0): ?>
                    <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_paciente'] ?></td>
                        <td>
        <a href="perfilPaciente.php?id=<?= $row['id_paciente'] ?>" 
           class="text-primary fw-semibold text-decoration-none">
            <?= htmlspecialchars($row['nombre']) ?>
        </a>
    </td>
                        <td><?= $row['edad'] ?></td>
                        <td><?= $row['sexo'] ?></td>
                        <td><?= $row['telefono'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['medico_solicitante'] ?></td>
                        <td><?= $row['fecha_registro'] ?></td>
                        <td class="text-center">

                            <div class="d-inline-flex gap-1">

                                <a href="editarPaciente.php?id=<?= $row['id_paciente'] ?>"
                                    class="btn btn-warning btn-sm">✏️ Editar</a>

                                <a href="accionesPacientes.php/eliminarPaciente.php?id=<?= $row['id_paciente'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Eliminar este paciente?')">🗑️ Eliminar</a>

                                <a href="/lab/pacientes/ordenesEstudios/crearOrden.php?id=<?= $row['id_paciente'] ?>"
                                    class="btn btn-success btn-sm">➕ Estudio</a>

                            </div>

                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No se encontraron pacientes</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>