<?php
require "../conexion.php";

// VALIDAR ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Paciente no válido.");
}

// OBTENER DATOS DEL PACIENTE
$stmt = $conexion->prepare("SELECT * FROM pacientes WHERE id_paciente = ?");
if (!$stmt) {
    die("Error en la consulta de paciente: " . $conexion->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$paciente) {
    die("Paciente no encontrado.");
}

/*
  OBTENER ESTUDIOS RELACIONADOS AL PACIENTE
  En el esquema que compartiste no existe la tabla 'estudios_realizados',
  así que usamos ordenes -> orden_estudios -> estudios para obtener
  el historial del paciente.
*/
$estudiosQuery = $conexion->prepare("
    SELECT 
        oe.id_orden_estudio,
        o.id_orden,
        o.fecha_creacion AS fecha_solicitud,
        oe.estado,
        oe.cancelado,
        est.nombre AS nombre_estudio
    FROM orden_estudios oe
    JOIN ordenes o ON oe.id_orden = o.id_orden
    JOIN estudios est ON est.id_estudio = oe.id_estudio
    WHERE o.id_paciente = ?
    ORDER BY o.fecha_creacion DESC
");
if (!$estudiosQuery) {
    die("Error en la consulta de estudios: " . $conexion->error);
}
$estudiosQuery->bind_param("i", $id);
$estudiosQuery->execute();
$estudios = $estudiosQuery->get_result();
$estudiosQuery->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil del Paciente</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

    body {
        background: #eef1f5;
        font-family: "Inter", sans-serif;
    }

    /* HEADER DEL PERFIL */
    .profile-header {
        background: white;
        padding: 35px 30px;
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.07);
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        background: #4e73df;
        color: white;
        font-size: 36px;
        font-weight: bold;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 0 10px rgba(255,255,255,0.3);
    }

    .section-title {
        margin-top: 45px;
        margin-bottom: 18px;
        font-size: 22px;
        font-weight: 600;
        color: #333;
        border-left: 5px solid #4e73df;
        padding-left: 10px;
    }

    /* TARJETAS DE DATOS */
    .data-card {
        background: white;
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.07);
        font-size: 15px;
    }

    /* TIMELINE DE ORDENES */
    .order-card {
        background: white;
        border-radius: 16px;
        padding: 22px 25px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        border-left: 5px solid #1cc88a;
    }

    .order-header {
        font-weight: 600;
        font-size: 18px;
        color: #444;
    }

    .order-date {
        color: #888;
        font-size: 14px;
        margin-top: -2px;
    }

    /* ESTUDIOS */
    .study-item {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 14px;
        margin-top: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 4px solid #4e73df;
    }

    /* STATUS */
    .status-pill {
        padding: 6px 13px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .status-pendiente { background: #f6c23e; }
    .status-en_proceso { background: #36b9cc; }
    .status-validado,
    .status-completo,
    .status-aprobado { background: #1cc88a; }
    .status-cancelado { background: #e74a3b; }

</style>
</head>
<body>

<div class="container py-5">

    <!-- PERFIL DEL PACIENTE -->
    <div class="profile-header mb-4">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($paciente["nombre"], 0, 1)); ?>
        </div>
        <div>
            <h2 class="mb-1"><?php echo htmlspecialchars($paciente["nombre"]); ?></h2>
            <span class="text-muted">ID Paciente: <?php echo $paciente["id_paciente"]; ?></span>
        </div>
    </div>

    <!-- DATOS -->
    <h3 class="section-title">Información General</h3>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="data-card">
                <strong>Edad:</strong><br><?php echo $paciente["edad"]; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-card">
                <strong>Teléfono:</strong><br><?php echo $paciente["telefono"]; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-card">
                <strong>Email:</strong><br><?php echo $paciente["email"]; ?>
            </div>
        </div>
        <div class="col-md-12">
            <div class="data-card">
                <strong>Dirección:</strong><br><?php echo $paciente["domicilio"]; ?>
            </div>
        </div>
    </div>

    <!-- HISTORIAL AGRUPADO POR ORDEN -->
   <!-- HISTORIAL AGRUPADO POR ORDEN -->
<h3 class="section-title">Historial de Órdenes y Estudios</h3>

<style>
    .order-card {
        background: white;
        border-radius: 18px;
        padding: 28px 30px;
        margin-bottom: 30px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        border-left: 6px solid #4e73df;
    }

    .order-header {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .order-date {
        font-size: 14px;
        color: #6c757d;
    }

    /* TARJETA DE ESTUDIO */
    .study-card {
        background: #f8f9fc;
        border-radius: 14px;
        padding: 18px 20px;
        margin-top: 14px;
        box-shadow: inset 0 0 0 1px #e1e5ea;
        transition: 0.2s ease;
    }

    .study-card:hover {
        background: #eef2ff;
        transform: translateX(4px);
        box-shadow: inset 0 0 0 1px #c7d2fe;
    }

    .study-title {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
    }

    .study-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* PASTILLAS DE ESTADO */
    .status-pill {
        padding: 5px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .status-pendiente { background: #f6c23e; }
    .status-capturado { background: #36b9cc; }
    .status-validado { background: #1cc88a; }
    .status-aprobado { background: #2ecc71; }
    .status-en_proceso { background: #4e73df; }
    .status-cancelado { background: #e74a3b; }

    /* Botón de descarga elegante */
    .btn-download {
        background: #4e73df;
        color: white;
        border-radius: 10px;
        padding: 6px 14px;
        border: none;
    }

    .btn-download:hover {
        background: #2e59d9;
        color: white;
    }

    .btn-download.disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        color: white;
    }
</style>

<?php

// AGRUPAR POR ORDEN
$ordenesAgrupadas = [];

while ($row = $estudios->fetch_assoc()) {

    $ordenId = $row["id_orden"];

    if (!isset($ordenesAgrupadas[$ordenId])) {
        $ordenesAgrupadas[$ordenId] = [
            "fecha" => $row["fecha_solicitud"],
            "estudios" => []
        ];
    }

    $ordenesAgrupadas[$ordenId]["estudios"][] = $row;
}
?>

<?php if (empty($ordenesAgrupadas)): ?>

    <div class="alert alert-secondary">El paciente no tiene órdenes registradas.</div>

<?php else: ?>

    <?php foreach ($ordenesAgrupadas as $ordenId => $orden): ?>

        <div class="order-card">

            <!-- ENCABEZADO DE LA ORDEN -->
            <div class="order-header">Orden #<?php echo $ordenId; ?></div>
            <div class="order-date">
                Fecha: <?php echo date("d/m/Y H:i", strtotime($orden["fecha"])); ?>
            </div>

            <!-- LISTA DE ESTUDIOS -->
            <?php foreach ($orden["estudios"] as $est): ?>

                <div class="study-card d-flex justify-content-between align-items-center">

                    <div>
                        <div class="study-title"><?php echo htmlspecialchars($est["nombre_estudio"]); ?></div>
                    </div>

                    <div class="study-actions">

                        <!-- ESTATUS -->
                        <span class="status-pill status-<?php echo strtolower($est["estado"]); ?>">
                            <?php echo ucfirst($est["estado"]); ?>
                        </span>

                        <!-- BOTÓN DE DESCARGA PDF -->
                        <?php 
                            // Aquí se agregará la ruta real en el futuro
                            $pdf = isset($est["pdf_path"]) ? $est["pdf_path"] : "";
                        ?>

                        <?php if (!empty($pdf)): ?>
                            <a href="<?= htmlspecialchars($pdf) ?>" target="_blank" class="btn btn-download">
                                Descargar
                            </a>
                        <?php else: ?>
                            <button class="btn btn-download disabled" disabled>
                                PDF
                            </button>
                        <?php endif; ?>

                    </div>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endforeach; ?>

<?php endif; ?>


</div>

</body>
</html>
