<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

require "../conexion.php";

// Obtener reactivos con stock calculado desde lotes
$res = $conexion->query("
    SELECT r.id_reactivo, r.nombre, r.descripcion, r.unidad, r.stock_minimo,
           COALESCE(SUM(l.cantidad), 0) AS stock_actual
    FROM reactivos r
    LEFT JOIN lotes_reactivos l ON r.id_reactivo = l.id_reactivo
    GROUP BY r.id_reactivo
    ORDER BY r.nombre ASC
");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Almacén</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">
    <link rel="stylesheet" href="/lab/css/almacen/almacen.css">
</head>

<body>

    <?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'Almacen'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">Almacén de Reactivos</h3>
            <a class="btn btn-primary" href="/lab/almacen/pantallas/agregarReactivo.php">
                <i class="bi bi-plus-lg"></i> Agregar Reactivo
            </a>
        </div>

        <div class="card-custom">
            <div class="table-container">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Unidad</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nombre']) ?></td>
                            <td><?= htmlspecialchars($r['descripcion']) ?></td>
                            <td><?= htmlspecialchars($r['unidad']) ?></td>
                            <td>
                                <span
                                    class="badge <?= $r['stock_actual'] <= $r['stock_minimo'] ? 'badge-low' : 'badge-ok' ?>">
                                    <?= $r['stock_actual'] ?>
                                </span>
                            </td>
                            <td><?= $r['stock_minimo'] ?></td>
                            <td>
                                <a href="pantallas/editarReactivo.php?id=<?= $r['id_reactivo'] ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="eliminarReactivo(<?= $r['id_reactivo'] ?>, '<?= addslashes($r['nombre']) ?>')">
                                    Eliminar
                                </button>

                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <button class="btn btn-secondary" onclick="history.back()">
    ← Regresar
</button>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function eliminarReactivo(id_reactivo, nombre) {
        Swal.fire({
            title: `¿Eliminar reactivo "${nombre}"?`,
            text: "Esta acción eliminará también todos los lotes y movimientos relacionados.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/lab/almacen/accionesAlmacen/eliminarReactivo.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id_reactivo=${id_reactivo}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Eliminado', 'El reactivo se eliminó correctamente.', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
                    });
            }
        });
    }
    </script>


</body>

</html>