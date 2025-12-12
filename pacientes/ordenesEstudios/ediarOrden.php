<?php
require "../../conexion.php";

// Validar orden
if (!isset($_GET["id"])) {
    die("ID de orden no proporcionado");
}

$id_orden = intval($_GET["id"]);

// Obtener datos de la orden
$sql = "SELECT o.*, p.nombre AS paciente_nombre, p.edad, p.sexo, p.telefono, p.email
        FROM ordenes o
        INNER JOIN pacientes p ON o.id_paciente = p.id_paciente
        WHERE o.id_orden = $id_orden";

$orden = $conexion->query($sql)->fetch_assoc();

// Obtener cita (si existe)
$sqlCita = "SELECT * FROM citas WHERE id_cita = " . intval($orden["id_cita"]);
$cita = $conexion->query($sqlCita)->fetch_assoc();

// Obtener estudios seleccionados
$sqlEst = "SELECT oe.id_orden_estudio, e.id_estudio, e.codigo, e.nombre, e.precio
           FROM orden_estudios oe
           INNER JOIN estudios e ON oe.id_estudio = e.id_estudio
           WHERE oe.id_orden = $id_orden";
$estudios = $conexion->query($sqlEst);

// Obtener catálogo de estudios para agregar
$catalogo = $conexion->query("SELECT * FROM estudios ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar orden</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet">
    <style>
        .section-title {
            font-size: 14px;
            font-weight: 600;
            padding: 6px;
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="bg-light">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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

    .btn-primary, .btn-success {
        border-radius: 6px;
    }

    /* Selector moderno */
    select.form-select {
        padding: 6px 10px;
        font-size: 0.9rem;
    }
</style>


<div class="container py-4" style="max-width: 920px;">

    <h4 class="fw-semibold mb-4">
        ✏ Editar Orden #<?= $orden["folio"] ?>
    </h4>

    <form action="/lab/pacientes/ordenesEstudios/administrarOrdenes/guardarCambiosOrden.php" method="POST">
        <input type="hidden" name="id_orden" value="<?= $id_orden ?>">

        <!-- ORDEN -->
        <div class="card p-3 mb-3">
            <div class="section-title">Datos de la Orden</div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label>Estado</label>
                    <select class="form-select" name="estado">
                        <option <?= $orden["estado"]=="pendiente"?"selected":"" ?>>pendiente</option>
                        <option <?= $orden["estado"]=="pagada"?"selected":"" ?>>pagada</option>
                        <option <?= $orden["estado"]=="en_proceso"?"selected":"" ?>>en_proceso</option>
                        <option <?= $orden["estado"]=="completa"?"selected":"" ?>>completa</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Folio</label>
                    <input type="text" class="form-control" value="<?= $orden["folio"] ?>" disabled>
                </div>

                <div class="col-md-3">
                    <label>Total actual</label>
                    <input type="text" class="form-control" value="$<?= number_format($orden["total"],2) ?>" disabled>
                </div>
            </div>
        </div>

        <!-- CITA -->
        <div class="card p-3 mb-3">
            <div class="section-title">Cita</div>
            <input type="hidden" name="id_cita" value="<?= $cita['id_cita'] ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Fecha de cita</label>
                    <input type="date" name="fecha_cita" class="form-control"
                           value="<?= $cita ? $cita["fecha_cita"] : "" ?>">
                </div>

                <div class="col-md-6">
                    <label>Hora de cita</label>
                    <input type="time" name="hora_cita" class="form-control"
                           value="<?= $cita ? $cita["hora_cita"] : "" ?>">
                </div>
            </div>
        </div>

        <!-- ESTUDIOS -->
        <div class="card p-3 mb-3">
            <div class="section-title">Estudios de la Orden</div>

            <table class="table table-sm table-bordered align-middle" id="tablaEstudios">
    <thead class="table-light">
        <tr>
            <th>Código</th>
            <th>Estudio</th>
            <th class="text-end">Precio</th>
            <th class="text-center" style="width: 70px;">Quitar</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($e = $estudios->fetch_assoc()): ?>
            <tr>
                <td><?= $e["codigo"] ?></td>
                <td><?= $e["nombre"] ?></td>
                <td class="text-end">$<?= number_format($e["precio"],2) ?></td>
                <td class="text-center">
                  <input type="checkbox" class="form-check-input chkQuitar"
       name="quitar_estudio[]" value="<?= $e["id_orden_estudio"] ?>">

                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>


            <div class="section-title mt-4">Agregar estudios</div>

            <div class="row g-2">
                <div class="col-md-9">
                    <select id="estudioSelect" class="form-select">
                        <option value="">Seleccione un estudio…</option>
                        <?php while ($c = $catalogo->fetch_assoc()): ?>
                            <option value="<?= $c["id_estudio"] ?>" data-precio="<?= $c["precio"] ?>">
                                <?= $c["codigo"] ?> — <?= $c["nombre"] ?>
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

        <div class="d-flex gap-2">
            <button class="btn btn-success px-4">💾 Guardar Cambios</button>
            <a href="/lab/estudios/estudios.php" class="btn btn-secondary">Volver</a>
        </div>

    </form>
</div>


<script>
let estudiosNuevos = [];

function actualizarHidden() {
    document.getElementById("agregar_estudios").value = JSON.stringify(estudiosNuevos);
}

// 🟡 FUNCIÓN GENERAL PARA "QUITAR" (EXISTENTE O NUEVO)
function activarEventoQuitar(checkbox) {
    checkbox.addEventListener("change", function() {
        const fila = this.closest("tr");

        if (this.checked) {
            fila.style.opacity = "0.4";
            fila.style.textDecoration = "line-through";

            // 🟥 SI ES NUEVO → QUITARLO DEL ARRAY
            if (fila.dataset.tipo === "nuevo") {
                estudiosNuevos = estudiosNuevos.filter(id => id != fila.dataset.id);
                actualizarHidden();
            }
        } else {
            fila.style.opacity = "1";
            fila.style.textDecoration = "none";

            // 🟩 SI ES NUEVO Y SE REACTIVA → VOLVER A AGREGAR
            if (fila.dataset.tipo === "nuevo") {
                estudiosNuevos.push(fila.dataset.id);
                actualizarHidden();
            }
        }
    });
}

// Activar quitar para estudios YA EXISTENTES
document.querySelectorAll(".chkQuitar").forEach(chk => activarEventoQuitar(chk));



// 🟢 AGREGAR ESTUDIO NUEVO A LA TABLA
document.getElementById("btnAgregar").onclick = function() {
    const select = document.getElementById("estudioSelect");
    const id = select.value;

    if (!id) { 
        alert("Seleccione un estudio");
        return;
    }

    const texto = select.options[select.selectedIndex].text;
    const precio = select.options[select.selectedIndex].dataset.precio;

    // Guardamos en el arreglo
    estudiosNuevos.push(id);
    actualizarHidden();

    // Crear fila nueva con checkbox de quitar
    const nuevaFila = document.createElement("tr");
    nuevaFila.classList.add("table-success");
    nuevaFila.dataset.tipo = "nuevo";  // para distinguir
    nuevaFila.dataset.id = id;

    nuevaFila.innerHTML = `
        <td>${texto.split("—")[0].trim()}</td>
        <td>${texto.split("—")[1].trim()}</td>
        <td class="text-end">$${parseFloat(precio).toFixed(2)}</td>
        <td class="text-center">
            <input type="checkbox" class="form-check-input chkQuitarNuevo">
        </td>
    `;

    // Insertamos la fila
    document.querySelector("#tablaEstudios tbody").appendChild(nuevaFila);

    // Activamos el evento de quitar para este estudio nuevo
    activarEventoQuitar(nuevaFila.querySelector(".chkQuitarNuevo"));

    // Reset del select
    select.value = "";
};
</script>






</body>
</html>
