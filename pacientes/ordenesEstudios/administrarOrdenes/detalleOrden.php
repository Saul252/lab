<?php
require "../../../conexion.php";

$id_orden = $_GET["id"] ?? 0;

$sql = "SELECT o.*, 
               p.nombre AS paciente_nombre,
               p.edad,
               p.sexo,
               p.telefono,
               p.email,
               p.domicilio,
               c.fecha_cita,
               c.hora_cita
        FROM ordenes o
        INNER JOIN pacientes p ON o.id_paciente = p.id_paciente
        LEFT JOIN citas c ON o.id_cita = c.id_cita
        WHERE o.id_orden = $id_orden";
$orden = $conexion->query($sql)->fetch_assoc();

$sqlEstudios = "SELECT e.codigo, e.nombre, e.precio, oe.estado
                FROM orden_estudios oe
                INNER JOIN estudios e ON oe.id_estudio = e.id_estudio
                WHERE oe.id_orden = $id_orden";
$estudios = $conexion->query($sqlEstudios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden #<?= $orden["folio"] ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { background: #f6f6f6; font-size: 0.9rem; }
    .card { border: 1px solid #e0e0e0; }
    .section-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: .35rem;
    }
    @media print {
        #acciones { display: none !important; }
        body { background: white; }
        .card { border: none; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

<div class="container py-3">

    <!-- ACCIONES -->
    <div id="acciones" class="mb-3 d-flex gap-2">
        <a href="/lab/estudios/estudios.php" class="btn btn-light border">Volver</a>
        <button onclick="window.print()" class="btn btn-dark">Imprimir</button>
    </div>

    <!-- ENCABEZADO -->
    <div class="card shadow-sm mb-3">
        <div class="card-body text-center py-3">
            <h5 class="mb-1 text-muted">Orden de Servicio</h5>
            <small class="text-secondary d-block mb-2">Folio: <?= $orden["folio"] ?></small>
            <svg id="barcode"></svg>
        </div>
    </div>

    <script>
        JsBarcode("#barcode", "<?= $orden['folio'] ?>", {
            format: "CODE128",
            width: 1.4,
            height: 45,
            displayValue: true,
            fontSize: 12
        });
    </script>

<!-- DATOS DEL PACIENTE -->
<div class="card shadow-sm mb-3">
    <div class="card-body">

        <div class="section-title bg-dark text-white text-center">Datos del Paciente</div>

        <div class="row g-2">
            <div class="col-6 col-md-6">
                <label class="text-muted small">Nombre</label>
                <div class="form-control"><?= $orden["paciente_nombre"] ?></div>
            </div>

            <div class="col-2 col-md-1">
                <label class="text-muted small">Edad</label>
                <div class="form-control"><?= $orden["edad"] ?></div>
            </div>

            <div class="col-2 col-md-1">
                <label class="text-muted small">Sexo</label>
                <div class="form-control"><?= $orden["sexo"] ?></div>
            </div>

            <div class="col-4 col-md-4">
                <label class="text-muted small">Teléfono</label>
                <div class="form-control"><?= $orden["telefono"] ?></div>
            </div>

            <div class="col-6 col-md-4">
                <label class="text-muted small">Email</label>
                <div class="form-control"><?= $orden["email"] ?></div>
            </div>

            <div class="col-6 col-md-4">
                <label class="text-muted small">Domicilio</label>
                <div class="form-control"><?= $orden["domicilio"] ?></div>
            </div>
        </div>

    </div>
</div>



  
    <!-- DATOS DE LA ORDEN -->
<div class="card shadow-sm mb-3">
    <div class="card-body">

        <div class="section-title bg-dark text-white text-center">Datos de la Orden</div>

        <div class="row g-2">
            <div class="col-6 col-md-4">
                <label class="text-muted small">Folio</label>
                <div class="form-control"><?= $orden["folio"] ?></div>
            </div>

            <div class="col-4 col-md-2">
                <label class="text-muted small">Estado</label>
                <div class="form-control"><?= ucfirst($orden["estado"]) ?></div>
            </div>

            <div class="col-4 col-md-2">
                <label class="text-muted small">Total</label>
                <div class="form-control fw-semibold">$<?= number_format($orden["total"],2) ?></div>
            </div>

            <div class="col-6 col-md-3">
                <label class="text-muted small">Creada el</label>
                <div class="form-control"><?= $orden["fecha_creacion"] ?></div>
            </div>
        </div>

    </div>
</div>


    <!-- CITA -->
   <!-- CITA -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="section-title bg-dark text-white text-center">Cita</div>

        <?php if ($orden["fecha_cita"]): ?>
            <div class="row g-2">
                <div class="col-6 col-md-6">
                    <label class="text-muted small">Fecha</label>
                    <div class="form-control"><?= $orden["fecha_cita"] ?></div>
                </div>

                <div class="col-6 col-md-6">
                    <label class="text-muted small">Hora</label>
                    <div class="form-control"><?= $orden["hora_cita"] ?></div>
                </div>
            </div>
        <?php else: ?>
            <p class="text-muted small mb-0">No se programó cita.</p>
        <?php endif; ?>
    </div>
</div>


    <!-- ESTUDIOS -->
    <div class="card shadow-sm">
        <div class="card-body">

            <div class="section-title bg-dark text-white text-center">Estudios Solicitados</div>

            <div class="table-responsive" style="max-height:280px;">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Estudio</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($e = $estudios->fetch_assoc()): ?>
                        <tr>
                            <td><?= $e["codigo"] ?></td>
                            <td><?= $e["nombre"] ?></td>
                            <td class="text-end">$<?= number_format($e["precio"],2) ?></td>
                            <td class="text-center"><?= ucfirst($e["estado"]) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

</body>
</html>
