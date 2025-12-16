<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

require "../conexion.php"; // AJUSTA LA RUTA

// ====================== CONTADORES ======================

// Usuarios
$usuarios_total = $conexion->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()["total"];

// Pacientes
$pacientes_total = $conexion->query("SELECT COUNT(*) AS total FROM pacientes")->fetch_assoc()["total"];

// Ordenes por estado
$ordenes = $conexion->query("
    SELECT 
        SUM(estado='pendiente') AS pendientes,
        SUM(estado='en_proceso') AS en_proceso,
        SUM(estado='completa') AS completas,
        SUM(estado='pagada') AS pagadas
    FROM ordenes
")->fetch_assoc();

// Citas del día
$hoy = date("Y-m-d");
$citas_hoy = $conexion->query("
    SELECT COUNT(*) AS total 
    FROM citas 
    WHERE fecha_cita = '$hoy'
")->fetch_assoc()["total"];

// Inventario
$insumos_total = $conexion->query("SELECT COUNT(*) AS total FROM reactivos")->fetch_assoc()["total"];
$insumos_en_cero = $conexion->query("SELECT COUNT(*) AS total FROM reactivos WHERE stock_actual = 0")->fetch_assoc()["total"];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Laboratorio</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

     <link rel="stylesheet" href="/lab/css/sidebar.css">
 <link rel="stylesheet" href="/lab/css/style.css">

    <!-- Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>
        body {
            background: #f4f5f7;
        }
        .card-custom {
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            transition: .2s;
        }
        .card-custom:hover {
            transform: translateY(-3px);
        }
        .chart-card {
            padding: 20px;
        }
    </style>
</head>

<body>
  <!-- NAVBAR -->
  <?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'Dashboard'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>
<div class="container py-4">

    <h2 class="mb-4 fw-bold">Dashboard General</h2>

    <!-- ============ TARJETAS SUPERIORES ============ -->
    <div class="row g-3">

        <div class="col-md-3">
            <a href="/lab/usuarios/administarUsuarios.php" class="text-decoration-none">
                <div class="card card-custom p-3 text-center bg-white">
                    <i class="bi bi-people fs-1 text-primary"></i>
                    <h4 class="mt-2 mb-0"><?= $usuarios_total ?></h4>
                    <small class="text-muted">Usuarios Registrados</small>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="/lab/pacientes/pacientes.php" class="text-decoration-none">
                <div class="card card-custom p-3 text-center bg-white">
                    <i class="bi bi-person-square fs-1 text-success"></i>
                    <h4 class="mt-2 mb-0"><?= $pacientes_total ?></h4>
                    <small class="text-muted">Pacientes Totales</small>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="/lab/estudios/estudios.php" class="text-decoration-none">
                <div class="card card-custom p-3 text-center bg-white">
                    <i class="bi bi-receipt fs-1 text-warning"></i>
                    <h4 class="mt-2 mb-0"><?= $ordenes["pendientes"] ?></h4>
                    <small class="text-muted">Ordenes Pendientes</small>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="/lab/almacen/reactivos.php" class="text-decoration-none">
                <div class="card card-custom p-3 text-center bg-white">
                    <i class="bi bi-box-seam fs-1 text-danger"></i>
                    <h4 class="mt-2 mb-0"><?= $insumos_en_cero ?></h4>
                    <small class="text-muted">Insumos en 0</small>
                </div>
            </a>
        </div>

    </div>



    <!-- ============ GRÁFICAS ============ -->
    <div class="row mt-4">

        <div class="col-md-6">
            <div class="card chart-card card-custom bg-white">
                <h5 class="mb-3">Estado de Ordenes</h5>
                <canvas id="chartOrdenes"></canvas>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card chart-card card-custom bg-white">
                <h5 class="mb-3">Inventario General</h5>
                <canvas id="chartInventario"></canvas>
            </div>
        </div>

    </div>

</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// =================== CHART ORDENES ===================
new Chart(document.getElementById('chartOrdenes'), {
    type: 'doughnut',
    data: {
        labels: ['Pendientes', 'En Proceso', 'Completas', 'Pagadas'],
        datasets: [{
            data: [
                <?= $ordenes["pendientes"] ?>,
                <?= $ordenes["en_proceso"] ?>,
                <?= $ordenes["completas"] ?>,
                <?= $ordenes["pagadas"] ?>
            ]
        }]
    }
});

// =================== CHART INVENTARIO ===================
new Chart(document.getElementById('chartInventario'), {
    type: 'bar',
    data: {
        labels: ['Total Insumos', 'En Cero'],
        datasets: [{
            data: [<?= $insumos_total ?>, <?= $insumos_en_cero ?>]
        }]
    }
});
</script>

</body>
</html>
