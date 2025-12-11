<?php
session_start();
require "../conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// filtros
$filtro = $conexion->real_escape_string($_GET['filtro'] ?? '');
$estado = $conexion->real_escape_string($_GET['estado'] ?? '');
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

// construir WHERE
$where = "1=1";
if ($filtro !== '') {
    $where .= " AND (o.folio LIKE '%$filtro%' OR p.nombre LIKE '%$filtro%')";
}
if ($estado !== '') {
    $estado_safe = $conexion->real_escape_string($estado);
    $where .= " AND o.estado = '$estado_safe'";
}
if ($desde !== '') {
    $desde_safe = $conexion->real_escape_string($desde);
    $where .= " AND DATE(o.fecha_creacion) >= '$desde_safe'";
}
if ($hasta !== '') {
    $hasta_safe = $conexion->real_escape_string($hasta);
    $where .= " AND DATE(o.fecha_creacion) <= '$hasta_safe'";
}

// consulta
$sql = "SELECT o.*, p.nombre AS paciente
        FROM ordenes o
        JOIN pacientes p ON o.id_paciente = p.id_paciente
        WHERE $where
        ORDER BY o.fecha_creacion DESC
        LIMIT 500";
$res = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de ordenes de estudios</title>
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
                                <a href="/lab/pacientes/pacientes.php" class="nav-link text-white  hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                        viewBox="0 0 16 16">
                                        <path d="M3 1h5l2 2h3v12H3z" />
                                    </svg>

                                    Pacientes
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/estudios/estudios.php" class="nav-link text-white active hoverbutton">
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
            <span class="navbar-brand">Ordenes de estudio</span>

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
    <h3>🧾 Órdenes / Estudios trabajados</h3>
    <a class="btn btn-success" href="orden_nueva.php">➕ Nueva Orden</a> 
    <a class="btn btn-success" href="/lab/estudios/listaEstudios.php"> Catalogo de Estudios</a>
  </div>

  <!-- FILTROS (no se modificó nada) -->
  <form class="row g-2 mb-3" method="GET">
    <div class="col-md-3"><input name="filtro" value="<?=htmlspecialchars($filtro)?>" class="form-control" placeholder="Folio o nombre paciente"></div>
    <div class="col-md-2">
      <select name="estado" class="form-select">
        <option value="">-- Estado --</option>
        <option value="pendiente" <?= $estado=='pendiente'?'selected':'' ?>>pendiente</option>
               <option value="en_proceso" <?= $estado=='en_proceso'?'selected':'' ?>>en_proceso</option>
        <option value="completa" <?= $estado=='completa'?'selected':'' ?>>completa</option>
      </select>
    </div>
    <div class="col-md-2"><input type="date" name="desde" value="<?=$desde?>" class="form-control"></div>
    <div class="col-md-2"><input type="date" name="hasta" value="<?=$hasta?>" class="form-control"></div>
    <div class="col-md-1"><button class="btn btn-primary w-100">Filtrar</button></div>
    <div class="col-md-2"><a href="estudios.php" class="btn btn-secondary w-100">Limpiar</a></div>
  </form>

 
  <!-- TABLA CON SCROLL -->
  <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
    <table class="table table-striped table-hover">
      <thead class="table-dark position-sticky top-0">
        <tr>
          <th>Folio</th>
          <th>Paciente</th>
          <th>Fecha</th>
          <th>Estado</th>
          <th>Total</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaDatos">
        <?php while($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['folio']) ?></td>
            <td><?= htmlspecialchars($row['paciente']) ?></td>
            <td><?= $row['fecha_creacion'] ?></td>
            <td><?= $row['estado'] ?></td>
            <td><?= number_format($row['total'],2) ?></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-info" href="orden_detalle.php?id_orden=<?= $row['id_orden'] ?>">Ver</a>
              <a class="btn btn-sm btn-primary" href="orden_editar.php?id_orden=<?= $row['id_orden'] ?>">Editar</a>
              <a class="btn btn-sm btn-secondary" href="orden_imprimir.php?id_orden=<?= $row['id_orden'] ?>">Imprimir</a>
              <button class="btn btn-sm btn-danger" onclick="confirmEliminarOrden(<?= $row['id_orden'] ?>)">Eliminar</button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
   <!-- Selector de paginación -->
  <div class="d-flex justify-content-end mb-2">
      <div style="width:150px">
          <select id="registrosPorPagina" class="form-select">
              <option value="10">Mostrar 10</option>
              <option value="20">Mostrar 20</option>
              <option value="50">Mostrar 50</option>
          </select>
      </div>
  </div>

</div>

<script>
/* PAGINACIÓN CLIENTE */
const select = document.getElementById('registrosPorPagina');
const filas = document.querySelectorAll('#tablaDatos tr');

function actualizarPaginacion() {
    const limite = parseInt(select.value);
    filas.forEach((fila, i) => fila.style.display = (i < limite ? '' : 'none'));
}

select.addEventListener('change', actualizarPaginacion);
actualizarPaginacion(); // inicial
</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>