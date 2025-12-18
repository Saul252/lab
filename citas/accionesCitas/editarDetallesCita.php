<?php
require "../../conexion.php";

/* =====================================================
   VALIDAR id_cita
   ===================================================== */
if (!isset($_GET["id_cita"])) {
    die("ID de cita no proporcionado");
}

$id_cita = intval($_GET["id_cita"]);

/* =====================================================
   OBTENER ORDEN + CITA + PACIENTE
   ===================================================== */
$sql = "
    SELECT 
        o.*,
        c.id_cita,
        c.fecha_cita,
        c.hora_cita,
        p.nombre AS paciente_nombre,
        p.edad,
        p.sexo,
        p.telefono,
        p.email
    FROM citas c
    INNER JOIN ordenes o ON c.id_cita = o.id_cita
    INNER JOIN pacientes p ON o.id_paciente = p.id_paciente
    WHERE c.id_cita = $id_cita
    LIMIT 1
";

$orden = $conexion->query($sql)->fetch_assoc();

if (!$orden) {
    die("No se encontró una orden asociada a esta cita");
}

$id_orden = $orden['id_orden'];

/* =====================================================
   ESTUDIOS DE LA ORDEN
   ===================================================== */
$sqlEst = "
    SELECT 
        oe.id_orden_estudio,
        e.id_estudio,
        e.codigo,
        e.nombre,
        e.precio
    FROM orden_estudios oe
    INNER JOIN estudios e ON oe.id_estudio = e.id_estudio
    WHERE oe.id_orden = $id_orden
";

$estudios = $conexion->query($sqlEst);

/* =====================================================
   CATÁLOGO DE ESTUDIOS
   ===================================================== */
$catalogo = $conexion->query("SELECT * FROM estudios ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar cita / orden</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f5f6f7;
    font-size: 0.9rem;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.section-title {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 12px;
    color: #495057;
    border-left: 3px solid #0d6efd;
    padding-left: 8px;
}

label {
    font-size: 13px;
    color: #6c757d;
}

.table-sm td, .table-sm th {
    padding: 6px 10px !important;
}
</style>
</head>

<body>

<div class="container py-4" style="max-width: 920px;">

    <h4 class="fw-semibold mb-4">
        ✏ Editar cita / orden — Folio <?= $orden["folio"] ?>
    </h4>

    <form action="/lab/pacientes/ordenesEstudios/administrarOrdenes/guardarCambiosOrden.php" method="POST">

        <!-- IDS -->
        <input type="hidden" name="id_orden" value="<?= $id_orden ?>">
        <input type="hidden" name="id_cita" value="<?= $orden['id_cita'] ?>">

        <!-- DATOS ORDEN -->
        <div class="card p-3 mb-3">
            <div class="section-title">Datos de la orden</div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label>Paciente</label>
                    <input type="text" class="form-control" value="<?= $orden['paciente_nombre'] ?>" disabled>
                </div>

                <div class="col-md-4">
                    <label>Folio</label>
                    <input type="text" class="form-control" value="<?= $orden['folio'] ?>" disabled>
                </div>

                <div class="col-md-4">
                    <label>Total actual</label>
                    <input type="text" class="form-control"
                           value="$<?= number_format($orden['total'], 2) ?>" disabled>
                </div>
            </div>
        </div>

        <!-- CITA -->
        <div class="card p-3 mb-3">
            <div class="section-title">Cita</div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Fecha de la cita</label>
                    <input type="date" name="fecha_cita" class="form-control"
                           value="<?= $orden['fecha_cita'] ?>">
                </div>

                <div class="col-md-6">
                    <label>Hora de la cita</label>
                    <input type="time" name="hora_cita" class="form-control"
                           value="<?= $orden['hora_cita'] ?>">
                </div>
            </div>
        </div>

        <!-- ESTUDIOS -->
        <div class="card p-3 mb-3">
            <div class="section-title">Estudios de la orden</div>

            <table class="table table-sm table-bordered align-middle" id="tablaEstudios">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Estudio</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center" style="width:70px">Quitar</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($e = $estudios->fetch_assoc()): ?>
                    <tr>
                        <td><?= $e['codigo'] ?></td>
                        <td><?= $e['nombre'] ?></td>
                        <td class="text-end">$<?= number_format($e['precio'],2) ?></td>
                        <td class="text-center">
                            <input type="checkbox"
                                   class="form-check-input chkQuitar"
                                   name="quitar_estudio[]"
                                   value="<?= $e['id_orden_estudio'] ?>">
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <!-- AGREGAR -->
            <div class="section-title mt-4">Agregar estudios</div>

            <div class="row g-2">
                <div class="col-md-9">
                    <select id="estudioSelect" class="form-select">
                        <option value="">Seleccione un estudio…</option>
                        <?php while ($c = $catalogo->fetch_assoc()): ?>
                            <option value="<?= $c['id_estudio'] ?>" data-precio="<?= $c['precio'] ?>">
                                <?= $c['codigo'] ?> — <?= $c['nombre'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3 d-grid">
                    <button type="button" id="btnAgregar" class="btn btn-primary">
                        ➕ Agregar
                    </button>
                </div>
            </div>

            <input type="hidden" name="agregar_estudios" id="agregar_estudios">
        </div>

        <!-- ACCIONES -->
        <div class="d-flex gap-2">
            <button class="btn btn-success px-4">💾 Guardar cambios</button>
            <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
        </div>

    </form>
</div>

<script>
let estudiosNuevos = [];

function actualizarHidden() {
    document.getElementById("agregar_estudios").value = JSON.stringify(estudiosNuevos);
}

function activarEventoQuitar(checkbox) {
    checkbox.addEventListener("change", function () {
        const fila = this.closest("tr");

        if (this.checked) {
            fila.style.opacity = "0.4";
            fila.style.textDecoration = "line-through";
        } else {
            fila.style.opacity = "1";
            fila.style.textDecoration = "none";
        }
    });
}

document.querySelectorAll(".chkQuitar").forEach(chk => activarEventoQuitar(chk));

document.getElementById("btnAgregar").onclick = function () {
    const select = document.getElementById("estudioSelect");
    if (!select.value) return alert("Seleccione un estudio");

    const texto = select.options[select.selectedIndex].text;
    const precio = select.options[select.selectedIndex].dataset.precio;
    const id = select.value;

    estudiosNuevos.push(id);
    actualizarHidden();

    const fila = document.createElement("tr");
    fila.classList.add("table-success");

    fila.innerHTML = `
        <td>${texto.split("—")[0].trim()}</td>
        <td>${texto.split("—")[1].trim()}</td>
        <td class="text-end">$${parseFloat(precio).toFixed(2)}</td>
        <td class="text-center">
            <input type="checkbox" class="form-check-input chkQuitar">
        </td>
    `;

    document.querySelector("#tablaEstudios tbody").appendChild(fila);
    activarEventoQuitar(fila.querySelector(".chkQuitar"));

    select.value = "";
};
</script>

</body>
</html>
