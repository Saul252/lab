<?php
require "../conexion.php";

// Validar ID de orden
$id_orden = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($id_orden <= 0) {
    die("Orden no válida.");
}

/* ==========================================================
   OBTENER ORDEN + PACIENTE
   ========================================================== */
$queryOrden = $conexion->prepare("
    SELECT 
        o.id_orden, o.folio, o.fecha_creacion,
        p.id_paciente, p.nombre, p.edad, p.sexo, p.telefono, p.email, p.domicilio
    FROM ordenes o
    JOIN pacientes p ON p.id_paciente = o.id_paciente
    WHERE o.id_orden = ?
");
$queryOrden->bind_param("i", $id_orden);
$queryOrden->execute();
$orden = $queryOrden->get_result()->fetch_assoc();

if (!$orden) {
    die("Orden no encontrada.");
}

/* ==========================================================
   OBTENER LISTA DE ESTUDIOS A REALIZAR
   ========================================================== */
$queryEstudios = $conexion->prepare("
    SELECT 
        oe.id_orden_estudio,
        oe.estado,
        est.nombre AS estudio,
        est.codigo
    FROM orden_estudios oe
    JOIN estudios est ON est.id_estudio = oe.id_estudio
    WHERE oe.id_orden = ?
    ORDER BY est.nombre ASC
");
$queryEstudios->bind_param("i", $id_orden);
$queryEstudios->execute();
$estudios = $queryEstudios->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Realizar Estudios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background-color: #f5f7fa;
    }
    .profile-box {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .avatar-circle {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #0d6efd;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 28px;
        font-weight: bold;
    }
    .study-card {
        background: white;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 15px;
        border-left: 6px solid #198754;
        box-shadow: 0 3px 12px rgba(0,0,0,0.07);
        transition: 0.2s;
    }
    .study-card:hover {
        transform: scale(1.01);
    }
    .status-pill {
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: bold;
    }
    .status-pendiente { background: #ffeeba; color: #856404; }
    .status-capturado { background: #cce5ff; color: #004085; }
    .status-validado { background: #d4edda; color: #155724; }
    .status-aprobado { background: #b8e0bc; color: #0f5132; }
</style>
</head>

<body>

<div class="container py-4">

    <!-- CAJA DEL PACIENTE -->
    <div class="profile-box mb-4">

        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($orden["nombre"], 0, 1)); ?>
            </div>

            <div>
                <h3 class="mb-0"><?php echo htmlspecialchars($orden["nombre"]); ?></h3>
                <small class="text-muted">Paciente ID: <?php echo $orden["id_paciente"]; ?></small><br>
                <small class="text-muted">Orden: <strong><?php echo $orden["folio"]; ?></strong></small>
            </div>
        </div>

        <hr>

        <div class="row mt-2">
            <div class="col-md-4 mb-2"><strong>Edad:</strong> <?php echo $orden["edad"]; ?></div>
            <div class="col-md-4 mb-2"><strong>Teléfono:</strong> <?php echo $orden["telefono"]; ?></div>
            <div class="col-md-4 mb-2"><strong>Correo:</strong> <?php echo $orden["email"]; ?></div>
            <div class="col-md-12 mb-2"><strong>Domicilio:</strong> <?php echo $orden["domicilio"]; ?></div>
        </div>
    </div>

    <!-- LISTA DE ESTUDIOS -->
    <h4 class="mb-3">Estudios a Realizar</h4>

    <?php if ($estudios->num_rows == 0): ?>
        <div class="alert alert-secondary">No hay estudios asignados a esta orden.</div>
    <?php endif; ?>

    <?php while ($e = $estudios->fetch_assoc()): ?>

        <?php
            // Clase según estado
            $statusClass = [
                'pendiente' => 'status-pendiente',
                'capturado' => 'status-capturado',
                'validado'  => 'status-validado',
                'aprobado'  => 'status-aprobado'
            ][$e["estado"]] ?? 'status-pendiente';
        ?>

        <div class="study-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($e["estudio"]); ?></h5>
                    <small class="text-muted">Código: <?php echo $e["codigo"]; ?></small>
                </div>

                <span class="status-pill <?php echo $statusClass; ?>">
                    <?php echo ucfirst($e["estado"]); ?>
                </span>
            </div>

            <div class="mt-3">
                <a href="/lab/laboratorio/resultados/capturarResultados.php?id=<?php echo $e['id_orden_estudio']; ?>" 
                   class="btn btn-success btn-sm">
                    Realizar estudio
                </a>

                <!-- Botón futuro para descargar -->
               <a href="/lab/laboratorio/paginas/interpretacionEstudio.php?id_orden_estudio=<?= $e['id_orden_estudio'] ?>"
   class="btn btn-sm btn-outline-primary">
   Ver resultado
</a>

            </div>
        </div>

    <?php endwhile; ?>

</div>

</body>
</html>
