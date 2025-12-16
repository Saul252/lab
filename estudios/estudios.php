<?php
session_start();
require "../conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

/* ================= AJAX ================= */
if (isset($_POST['ajax'])) {

    $filtro = $conexion->real_escape_string($_POST['filtro'] ?? '');
    $estado = $_POST['estado'] ?? '';
    $desde  = $_POST['desde'] ?? '';
    $hasta  = $_POST['hasta'] ?? '';

    $where = "1=1";
    $having = "1=1";

    if ($filtro !== '') {
        $where .= " AND (o.folio LIKE '%$filtro%' OR p.nombre LIKE '%$filtro%')";
    }

    if ($desde !== '') {
        $where .= " AND c.fecha_cita >= '$desde'";
    }

    if ($hasta !== '') {
        $where .= " AND c.fecha_cita <= '$hasta'";
    }

    $sql = "
    SELECT 
        o.id_orden,
        o.folio,
        o.total,

        p.nombre AS paciente,
        c.fecha_cita,
        c.hora_cita,
        c.estado AS estado_cita,

        COUNT(oe.id_orden_estudio) AS total_estudios,
        SUM(CASE WHEN oe.estado = 'capturado' THEN 1 ELSE 0 END) AS realizados

    FROM ordenes o
    JOIN pacientes p ON p.id_paciente = o.id_paciente
    LEFT JOIN citas c ON c.id_cita = o.id_cita
    LEFT JOIN orden_estudios oe ON oe.id_orden = o.id_orden
    WHERE $where
    GROUP BY o.id_orden
    ";

    if ($estado === 'completa') {
        $having .= " AND realizados = total_estudios AND total_estudios > 0";
    } elseif ($estado === 'pendiente') {
        $having .= " AND realizados = 0";
    } elseif ($estado === 'en_proceso') {
        $having .= " AND realizados > 0 AND realizados < total_estudios";
    }

    $sql .= " HAVING $having ORDER BY o.id_orden DESC LIMIT 500";

    $res = $conexion->query($sql);

    while ($row = $res->fetch_assoc()):
    ?>
<tr>
    <td><?= htmlspecialchars($row['folio']) ?></td>
    <td><?= htmlspecialchars($row['paciente']) ?></td>

    <td>
        <?= $row['fecha_cita']
                ? $row['fecha_cita'].' '.$row['hora_cita']
                : '<span class="text-muted">Sin cita</span>' ?>
    </td>

    <td>
        <?php
            if ($row['total_estudios'] > 0 && $row['realizados'] == $row['total_estudios']) {
                echo '<span class="badge bg-success">COMPLETA</span>';
            } elseif ($row['realizados'] > 0) {
                echo '<span class="badge bg-warning text-dark">EN PROCESO</span>';
            } else {
                echo '<span class="badge bg-secondary">PENDIENTE</span>';
            }
            ?>
    </td>

    <td>
        <span class="badge bg-success"><?= $row['realizados'] ?></span>
        /
        <span class="badge bg-secondary"><?= $row['total_estudios'] ?></span>
    </td>

    <td>$<?= number_format($row['total'],2) ?></td>

    <td class="text-nowrap">
        <a class="btn btn-sm btn-info"
            href="/lab/pacientes/ordenesEstudios/administrarOrdenes/detalleOrden.php?id=<?= $row['id_orden'] ?>">
            Ver
        </a>

        <?php if ($row['estado_cita'] !== 'cancelada'): ?>
        <a class="btn btn-sm btn-success" href="/lab/estudios/realizarEstudio.php?id=<?= $row['id_orden'] ?>">
            Realizar
        </a>
        <?php else: ?>
        <button class="btn btn-sm btn-secondary" disabled>
            Cancelada
        </button>
        <?php endif; ?>

<a class="btn btn-sm btn-warning"
            href="/lab/pacientes/ordenesEstudios/ediarOrden.php?id=<?= $row['id_orden'] ?>">
            Editar </a>
        <a class="btn btn-sm btn-danger"
            href="/lab/pacientes/ordenesEstudios/administrarOrdenes/eliminarOrden.php?id=<?= $row['id_orden'] ?>">
            Eliminar </a>
    </td>
</tr>
<?php
    endwhile;
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Órdenes de Estudios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">

</head>

<body class="bg-light">

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
                                <a href="/lab/bienvenida.php" class="nav-link text-white  hoverbutton">
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
                                <a href="/lab/dashboard/dashboard.php" class="nav-link text-white  hoverbutton">
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
                                <a href="/lab/citas/citas.php" class="nav-link text-white  hoverbutton">
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

            <span class="navbar-brand">Estudios</span>

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
    <div class="container mt-4">



        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>🧾 Órdenes / Estudios</h4>
            <div class="d-flex gap-2"> <a class="btn btn-success" href="/lab/pacientes/pacientes.php">➕ Nueva Orden</a>
                <a class="btn btn-outline-secondary" href="/lab/estudios/catalogoEstudios.php">Catálogo de Estudios</a>
            </div>
        </div>
        <form id="filtrosForm" class="row g-2 mb-3 align-items-end">

            <div class="col-md-3">
                <label class="form-label small">Folio / Paciente</label>
                <input name="filtro" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label small">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En proceso</option>
                    <option value="completa">Completa</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small">Desde</label>
                <input type="date" name="desde" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label small">Hasta</label>
                <input type="date" name="hasta" class="form-control">
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="button" id="limpiar" class="btn btn-outline-secondary w-100">
                    Limpiar
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Folio</th>
                        <th>Paciente</th>
                        <th>Cita</th>
                        <th>Estado</th>
                        <th>Estudios</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaResultados"></tbody>
            </table>
        </div>

    </div>

    <script>
    const form = document.getElementById('filtrosForm');
    const tabla = document.getElementById('tablaResultados');
    const limpiar = document.getElementById('limpiar');

    let timer = null;

    function cargar() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const data = new FormData(form);
            data.append('ajax', 1);

            fetch('estudios.php', {
                    method: 'POST',
                    body: data
                })
                .then(r => r.text())
                .then(html => {
                    tabla.innerHTML = html || `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    Sin resultados
                </td>
            </tr>`;
                });
        }, 300);
    }

    form.querySelectorAll('input,select').forEach(el => {
        el.addEventListener('input', cargar);
        el.addEventListener('change', cargar);
    });

    limpiar.addEventListener('click', () => {
        form.reset();
        cargar();
    });

    cargar();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
let autoRefresh = null;
let usuarioInteractuando = false;

// Detectar si el usuario está escribiendo
form.querySelectorAll('input,select').forEach(el => {
    el.addEventListener('focus', () => usuarioInteractuando = true);
    el.addEventListener('blur', () => usuarioInteractuando = false);
});

// Auto refresco cada 5 segundos
function iniciarAutoRefresh() {
    autoRefresh = setInterval(() => {
        if (!usuarioInteractuando) {
            cargar();
        }
    }, 5000); // ⏱ 5 segundos
}

iniciarAutoRefresh();
</script>

</body>

</html>