<?php
session_start();


 if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "admin") {
     header("Location: /lab/login.php");
     exit();
 }
require "../conexion.php";

// Obtener estudios
$sql = "SELECT * FROM estudios ORDER BY nombre ASC";
$estudios = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Catálogo de Estudios</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="/lab/css/styleCatalogoEstudios.css" rel="stylesheet">

<style>

</style>
</head>

<body class="bg-light">

<div class="container mt-4">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h4 class="mb-0">Catálogo de Estudios</h4>
            <a href="agregarTipoEstudio.php" class="btn btn-light btn-sm">➕ Nuevo Estudio</a>
        </div>

        <div class="card-body">

            <input type="text" id="buscar" class="form-control mb-3" placeholder="Buscar estudio por nombre, código o descripción...">

            <div class="table-container border rounded">
    <table class="table table-striped table-hover mb-0">
        <thead class="table-light sticky-top">
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Tipo</th>

                <!-- 🔹 Nuevas columnas -->
                <th>Hombre<br><small>(mín - máx)</small></th>
                <th>Mujer<br><small>(mín - máx)</small></th>

                <th class="col-desc">Descripción</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>

        <tbody id="tablaEstudios">
            <?php while ($e = $estudios->fetch_assoc()): ?>
            <tr 
                data-nombre="<?= strtolower($e['nombre']) ?>"
                data-codigo="<?= strtolower($e['codigo']) ?>"
                data-desc="<?= strtolower($e['descripcion']) ?>"
            >

                <td><?= $e['codigo'] ?></td>
                <td class="col-desc"><?= $e['nombre'] ?></td>
                <td>$<?= number_format($e['precio'],2) ?></td>
                <td><?= $e['tipo_resultado'] ?></td>

                <!-- 🔹 Valores para hombres -->
                <td>
                    <?= $e[ 'rango_hombre_min'] ?> - <?= $e['rango_hombre_max'] ?>
                </td>

                <!-- 🔹 Valores para mujeres -->
                <td>
                    <?= $e['rango_mujer_min'] ?> - <?= $e['rango_mujer_max'] ?>
                </td>

                <td class="col-desc" title="<?= $e['descripcion'] ?>">
                    <?= $e['descripcion'] ?>
                </td>

                <td class="text-center">
                    <a href="editar_estudio.php?id=<?= $e['id_estudio'] ?>"
                       class="btn btn-warning btn-sm">✏️ Editar</a>

                    <button class="btn btn-danger btn-sm"
                        onclick="eliminarEstudio(<?= $e['id_estudio'] ?>)">
                        🗑️ Eliminar
                    </button>
                </td>

            </tr>
            <?php endwhile; ?>
        </tbody>

    </table>
</div>


        </div>
    </div>

</div>

<script>
// FILTRO
document.getElementById("buscar").addEventListener("input", function(){
    const q = this.value.toLowerCase();

    document.querySelectorAll("#tablaEstudios tr").forEach(row => {
        const match =
            row.dataset.nombre.includes(q) ||
            row.dataset.codigo.includes(q) ||
            row.dataset.desc.includes(q);

        row.style.display = match ? "" : "none";
    });
});

// SWEETALERT ELIMINAR
function eliminarEstudio(id){
    Swal.fire({
        title: "¿Eliminar estudio?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(res => {
        if(res.isConfirmed){
            window.location = "eliminar_estudio.php?id=" + id;
        }
    });
}
</script>

</body>
</html>
