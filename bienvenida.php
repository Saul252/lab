<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">
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
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">

            <button class="btn btn-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">



                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white" class="bi bi-list"
                    viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M2.5 12.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11z" />
                </svg>

                Menu
            </button>

            <div class="offcanvas offcanvas-start bg-dark" data-bs-scroll="true" tabindex="-1"
                id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">

                <div class="offcanvas-header">
                    <h5 class="offcanvas-title text-white bg-dark" id="offcanvasWithBothOptionsLabel">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>

                <div class="offcanvas-body">

                    <?php if ($_SESSION["rol"] == "admin") { ?>

                    <div class="d-flex flex-column p-3 text-white bg-dark" style="height: 90vh; width: 100%;">

                        <ul class="nav nav-pills flex-column mb-auto">

                            <li class="nav-item">
                                <a href="/lab/bienvenida.php" class="nav-link text-white active hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 9l9-6 9 6"></path>
                                        <path d="M9 22V12h6v10"></path>
                                        <path d="M3 9v12h18V9"></path>
                                    </svg>

                                    Inicio
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/lab/dashboard/dashboard.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="me-2">
                                        <rect x="2" y="2" width="6" height="6" rx="1"></rect>
                                        <rect x="10" y="2" width="4" height="9" rx="1"></rect>
                                        <rect x="2" y="10" width="6" height="4" rx="1"></rect>
                                        <rect x="10" y="12" width="4" height="3" rx="1"></rect>
                                    </svg>
                                    Dashboard
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/usuarios/administarUsuarios.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white"
                                        class="bi bi-people-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0-2-5.235A3 3 0 0 0 11 8z" />
                                        <path
                                            d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z" />
                                        <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
                                    </svg>
                                    Usuarios
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/pacientes/pacientes.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                        viewBox="0 0 16 16">
                                        <path d="M3 1h5l2 2h3v12H3z" />
                                    </svg>
                                    Pacientes
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/estudios/estudios.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="me-2">
                                        <rect x="3" y="2" width="10" height="14" rx="1"></rect>
                                        <line x1="5" y1="6" x2="11" y2="6"></line>
                                        <line x1="5" y1="9" x2="11" y2="9"></line>
                                        <line x1="5" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Estudios
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/citas/citas.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <path d="M16 2v4M8 2v4M3 10h18"></path>
                                        <circle cx="12" cy="16" r="3"></circle>
                                        <path d="M12 14v2l1 1"></path>
                                    </svg>
                                    Citas
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/almacen/almacen.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                        viewBox="0 0 16 16">
                                        <path d="M2 3l6-2 6 2v10l-6 2-6-2V3z" />
                                        <path d="M2 3l6 2 6-2" />
                                    </svg>
                                    Almacén
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/caja/caja.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="me-2">
                                        <path d="M2 4l6-2 6 2v10l-6 2-6-2z"></path>
                                        <path d="M2 4l6 3 6-3"></path>
                                    </svg>
                                    Caja
                                </a>
                            </li>

                        </ul>

                        <hr>

                        <div class="dropup">
                            <a href="#"
                                class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                                id="userMenu" data-bs-toggle="dropdown">

                                <img src="https://github.com/mdo.png" alt="" width="32" height="32"
                                    class="rounded-circle me-2">

                                <strong><?php echo $_SESSION['usuario']; ?></strong>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                                <li><a class="dropdown-item" href="#">Perfil</a></li>
                                <li><a class="dropdown-item" href="#">Ajustes</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/lab/logout.php">Cerrar sesión</a></li>
                            </ul>
                        </div>

                    </div>

                    <?php } ?>

                </div>
            </div>

            <span class="navbar-brand">Inicio</span>

            <div class="dropdown me-4">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                    id="userMenu" data-bs-toggle="dropdown">

                    <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">

                    <strong><?php echo $_SESSION['usuario']; ?></strong>
                </a>

                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="/lab/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>

        </div>
    </nav>

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