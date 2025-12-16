<?php
session_start();
require "../../conexion.php";

if(!isset($_SESSION['usuario'])){
    header("Location: ../../login.php");
    exit;
}

$id_reactivo = $_GET['id'] ?? null;
if(!$id_reactivo){
    echo "ID de reactivo no proporcionado";
    exit;
}

// Datos generales del reactivo
$reactivo = $conexion->query("SELECT * FROM reactivos WHERE id_reactivo = $id_reactivo")->fetch_assoc();

// Lotes asociados
$lotes = $conexion->query("SELECT * FROM lotes_reactivos WHERE id_reactivo = $id_reactivo ORDER BY fecha_caducidad ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reactivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <link rel="stylesheet" href="/lab/css/almacen/editarReactivo.css">
   
</head>
<body>
<div class="container py-4">

    <h3 class="mb-4 text-primary">Editar Reactivo</h3>

    <!-- Card Reactivo -->
    <div class="card card-custom p-4">
        <form id="form-editar-reactivo">
            <input type="hidden" name="id_reactivo" value="<?= $reactivo['id_reactivo'] ?>">

            <h5 class="mb-3 text-secondary">Datos Generales</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($reactivo['nombre']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Unidad</label>
                    <input type="text" name="unidad" class="form-control" value="<?= htmlspecialchars($reactivo['unidad']) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($reactivo['descripcion']) ?></textarea>
            </div>
            <div class="mb-3 col-md-3">
                <label class="form-label">Stock Mínimo</label>
                <input type="number" name="stock_minimo" class="form-control" value="<?= $reactivo['stock_minimo'] ?>">
            </div>

            <hr>
            <h5 class="mb-3 text-secondary">Lotes Asociados</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Fecha Caducidad</th>
                            <th>Cantidad</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($lote = $lotes->fetch_assoc()): ?>
                        <tr data-id="<?= $lote['id_lote'] ?>">
                            <td><input type="text" class="form-control" name="numero_lote_<?= $lote['id_lote'] ?>" value="<?= htmlspecialchars($lote['numero_lote']) ?>"></td>
                            <td><input type="date" class="form-control" name="fecha_caducidad_<?= $lote['id_lote'] ?>" value="<?= $lote['fecha_caducidad'] ?>"></td>
                            <td><input type="number" class="form-control" name="cantidad_<?= $lote['id_lote'] ?>" value="<?= $lote['cantidad'] ?>"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-eliminar-lote"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="../../almacen/almacen.php" class="btn btn-secondary btn-custom">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-custom">Actualizar Reactivo</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('form-editar-reactivo').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/lab/almacen/accionesAlmacen/actualizarReactivo.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: 'El reactivo y sus lotes se actualizaron correctamente.',
                confirmButtonColor: '#3085d6'
            }).then(() => window.location.href = '../../almacen/almacen.php');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error inesperado',
            confirmButtonColor: '#d33'
        });
    });
});

// Eliminar lote visualmente
document.querySelectorAll('.btn-eliminar-lote').forEach(btn => {
    btn.addEventListener('click', function(){
        const row = this.closest('tr');
        row.remove();
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
