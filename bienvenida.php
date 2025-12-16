<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'Inicio'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">


    <style>
    body {
        background: #f5f6fa;
    }

    .dashboard-card {
        transition: .3s;
        border: none;
        border-radius: 12px;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .icon-box {
        width: 60px;
        height: 60px;
        background: #e9ecef;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: #4a4a4a;
    }

    .section-title span {
        border-bottom: 3px solid #0d6efd;
        padding-bottom: 4px;
    }
    </style>
</head>

<body>

    <!-- NAVBAR -->
  
    <!-- CONTENIDO -->
    <div class="container py-5">

        <h2 class="mb-5 text-center fw-bold">Bienvenido, <?php echo $_SESSION["usuario"]; ?></h2>

        <!-- SECCIÓN PRINCIPAL -->
        <h4 class="section-title mb-4 text-center"><span>Accesos Rápidos</span></h4>

        <div class="row g-4">

            <!-- Dashboard -->
            <div class="col-md-4">
                <a href="/lab/dashboard/dashboard.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-grid"></i></div>
                        <h5 class="fw-bold">Dashboard</h5>
                        <p class="text-muted">Panel de estadísticas y control.</p>
                    </div>
                </a>
            </div>

            <!-- Usuarios -->
            <div class="col-md-4">
                <a href="/lab/usuarios/administarUsuarios.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-people-fill"></i></div>
                        <h5 class="fw-bold">Usuarios</h5>
                        <p class="text-muted">Gestión de cuentas y permisos.</p>
                    </div>
                </a>
            </div>

            <!-- Pacientes -->
            <div class="col-md-4">
                <a href="/lab/pacientes/pacientes.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-person-vcard"></i></div>
                        <h5 class="fw-bold">Pacientes</h5>
                        <p class="text-muted">Consulta y registro de pacientes.</p>
                    </div>
                </a>
            </div>

            <!-- Estudios -->
            <div class="col-md-4">
                <a href="/lab/estudios/estudios.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-journal-text"></i></div>
                        <h5 class="fw-bold">Estudios</h5>
                        <p class="text-muted">Listado y administración de estudios.</p>
                    </div>
                </a>
            </div>

            <!-- Citas -->
            <div class="col-md-4">
                <a href="/lab/citas/citas.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-calendar-check"></i></div>
                        <h5 class="fw-bold">Citas</h5>
                        <p class="text-muted">Agenda de citas del laboratorio.</p>
                    </div>
                </a>
            </div>
  <div class="col-md-4">
                <a href="/lab/laboratorio/laboratorio.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"> <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                    class="bi bi-flask" viewBox="0 0 16 16">
                                    <path
                                        d="M6.5 1a.5.5 0 0 1 .5.5V5h2V1.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 .5.5v1.664a2 2 0 0 1-.586 1.414l-2.828 2.828A1 1 0 0 0 7 11v2h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h3v-2a1 1 0 0 0-.707-.293l-2.828-2.828A2 2 0 0 1 4 6.664V5.5a.5.5 0 0 1 .5-.5H5V1.5a.5.5 0 0 1 .5-.5z" />
                                </svg></div>
                        <h5 class="fw-bold">Laboratorio</h5>
                        <p class="text-muted">Lista de estudios a realizar.</p>
                    </div>
                </a>
            </div>

            <!-- Almacén -->
            <div class="col-md-4">
                <a href="/lab/almacen/almacen.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-box-seam"></i></div>
                        <h5 class="fw-bold">Almacén</h5>
                        <p class="text-muted">Control de inventario y suministros.</p>
                    </div>
                </a>
            </div>

            <!-- Caja -->
            <div class="col-md-4">
                <a href="/lab/caja/caja.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-cash-stack"></i></div>
                        <h5 class="fw-bold">Caja</h5>
                        <p class="text-muted">Control de pagos y facturación.</p>
                    </div>
                </a>
            </div>
        </div>


        <!-- SECCIÓN TUTORIALES Y MANUALES -->
        <h4 class="section-title mt-5 mb-4 text-center"><span>Recursos de Ayuda</span></h4>

        <div class="row g-4">

            <!-- Tutoriales -->
            <div class="col-md-6">
                <a href="tutoriales.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-play-circle"></i></div>
                        <h5 class="fw-bold">Tutoriales</h5>
                        <p class="text-muted">Guías en video paso a paso.</p>
                    </div>
                </a>
            </div>

            <!-- Manuales -->
            <div class="col-md-6">
                <a href="manuales.php" class="text-decoration-none">
                    <div class="card dashboard-card p-4 text-center">
                        <div class="icon-box mx-auto mb-3"><i class="bi bi-journal-bookmark"></i></div>
                        <h5 class="fw-bold">Manuales</h5>
                        <p class="text-muted">Documentación completa del sistema.</p>
                    </div>
                </a>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>