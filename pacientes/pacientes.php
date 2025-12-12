<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

require "../conexion.php";

// =============================
// FILTRO DE BÚSQUEDA
// =============================
$busqueda = "";
$where = "1";

if (isset($_GET["buscar"]) && !empty($_GET["buscar"])) {
    $busqueda = $conexion->real_escape_string($_GET["buscar"]);
    $where = "nombre LIKE '%$busqueda%' 
              OR email LIKE '%$busqueda%' 
              OR telefono LIKE '%$busqueda%'";
}

$sql = "SELECT * FROM pacientes WHERE $where ORDER BY fecha_registro DESC";
$resultado = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pacientes Registrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <button class="btn btn-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">

                <i class="bi bi-list" style="font-size: 1.2rem;"></i> <svg xmlns="http://www.w3.org/2000/svg" width="28"
                    height="28" fill="white" class="bi bi-list" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M2.5 12.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11z" />
                </svg>
                Menu

            </button>

            <div class="offcanvas offcanvas-start bg-dark" data-bs-scroll="true" tabindex="-1"
                id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title text-white bg-dark" id="offcanvasWithBothOptionsLabel">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>

                </div>
                <div class="offcanvas-body">
                    <?php if ($_SESSION["rol"] == "admin") { ?>

                    <div class="d-flex flex-column p-3 text-white bg-dark" style="height: 90vh; width: 100%;">

                        <!-- Título o logo -->

                        <ul class="nav nav-pills flex-column mb-auto">

                            <li class="nav-item">
                                <a href="/lab/bienvenida.php" class="nav-link text-white  hoverbutton">
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
                                <a href="/lab/usuarios/administarUsuarios.php" class="nav-link text-white  hoverbutton">
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
                                <a href="/lab/pacientes/pacientes.php" class="nav-link text-white active hoverbutton">
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
                                    <!-- Icono SVG: Citas (calendario + reloj) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        viewBox="0 0 24 24" aria-hidden="true" role="img">
                                        <!-- Calendario -->
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <path d="M16 2v4M8 2v4M3 10h18"></path>
                                        <!-- Reloj pequeño -->
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

                        <!-- Perfil -->
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
            <span class="navbar-brand">Pacientes</span>

            <div>
                <div style=" margin-right: 50px !important;" class="dropdown">
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
        </div>
    </nav>

    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Pacientes Registrados</h2>
            <a href="agregarPaciente.php" class="btn btn-success">➕ Agregar Paciente</a>
        </div>

        <!-- BUSCADOR -->
        <form class="row g-3 mb-4" method="GET">
            <div class="col-md-4">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar nombre, email, teléfono"
                    value="<?= $busqueda ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Buscar</button>
            </div>
            <div class="col-md-2">
                <a href="/lab/pacientes/pacientes.php" class="btn btn-secondary w-100">Limpiar</a>
            </div>
        </form>

        <!-- TABLA -->
       <div class="table-responsive scroll-tabla">

            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Médico</th>
                        <th>Fecha</th>
                        <th>Opciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                    <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_paciente'] ?></td>
                        <td>
        <a href="perfilPaciente.php?id=<?= $row['id_paciente'] ?>" 
           class="text-primary fw-semibold text-decoration-none">
            <?= htmlspecialchars($row['nombre']) ?>
        </a>
    </td>
                        <td><?= $row['edad'] ?></td>
                        <td><?= $row['sexo'] ?></td>
                        <td><?= $row['telefono'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['medico_solicitante'] ?></td>
                        <td><?= $row['fecha_registro'] ?></td>
                        <td class="text-center">

                            <div class="d-inline-flex gap-1">

                                <a href="editarPaciente.php?id=<?= $row['id_paciente'] ?>"
                                    class="btn btn-warning btn-sm">✏️ Editar</a>

                                <a href="accionesPacientes.php/eliminarPaciente.php?id=<?= $row['id_paciente'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('¿Eliminar este paciente?')">🗑️ Eliminar</a>

                                <a href="/lab/pacientes/ordenesEstudios/crearOrden.php?id=<?= $row['id_paciente'] ?>"
                                    class="btn btn-success btn-sm">➕ Estudio</a>

                            </div>

                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No se encontraron pacientes</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>