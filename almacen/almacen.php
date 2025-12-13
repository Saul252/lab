<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
require "../conexion.php";

// Obtener insumos
$res = $conexion->query("SELECT * FROM reactivos ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Almacén</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">

    <style>
        body {
            background: #f4f5f7;
        }
        .card-custom {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }
        thead th {
            position: sticky;
            top: 0;
            background: #e9ecef;
            z-index: 10;
        }
    </style>

</head>
<body>
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
                                <a href="/lab/almacen/almacen.php" class="nav-link text-white active hoverbutton">
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

            <span class="navbar-brand">Almacen</span>

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
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Almacén de Reactivos</h3>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            + Agregar Insumo
        </button>
    </div>

    <div class="card-custom">

        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Unidad</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th width="140">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($r = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['nombre'] ?></td>
                        <td><?= $r['descripcion'] ?></td>
                        <td><?= $r['unidad'] ?></td>
                        <td>
                            <span class="badge bg-<?= $r['stock_actual'] <= 0 ? 'danger' : 'secondary' ?>">
                                <?= $r['stock_actual'] ?>
                            </span>
                        </td>
                        <td><?= $r['stock_minimo'] ?></td>

                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="abrirEditar(<?= htmlspecialchars(json_encode($r)) ?>)">
                                Editar
                            </button>

                            <a href="eliminar_reactivo.php?id=<?= $r['id_reactivo'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Eliminar este reactivo?')">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>



<!-- ======================= MODAL AGREGAR ======================= -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="guardar_reactivo.php" method="POST">

            <div class="modal-header">
                <h5 class="modal-title">Agregar Insumo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" required>

                <label class="form-label mt-3">Descripción</label>
                <textarea name="descripcion" class="form-control"></textarea>

                <label class="form-label mt-3">Unidad</label>
                <input type="text" name="unidad" class="form-control">

                <label class="form-label mt-3">Stock Actual</label>
                <input type="number" name="stock_actual" class="form-control" required>

                <label class="form-label mt-3">Stock Mínimo</label>
                <input type="number" name="stock_minimo" class="form-control">

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary">Guardar</button>
            </div>

        </form>
    </div>
</div>


<!-- ======================= MODAL EDITAR ======================= -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="actualizar_reactivo.php" method="POST">

            <div class="modal-header">
                <h5 class="modal-title">Editar Insumo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="id_reactivo" id="edit_id">

                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" id="edit_nombre" class="form-control" required>

                <label class="form-label mt-3">Descripción</label>
                <textarea name="descripcion" id="edit_descripcion" class="form-control"></textarea>

                <label class="form-label mt-3">Unidad</label>
                <input type="text" name="unidad" id="edit_unidad" class="form-control">

                <label class="form-label mt-3">Stock Actual</label>
                <input type="number" name="stock_actual" id="edit_stock_actual" class="form-control" required>

                <label class="form-label mt-3">Stock Mínimo</label>
                <input type="number" name="stock_minimo" id="edit_stock_minimo" class="form-control">

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary">Actualizar</button>
            </div>

        </form>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ================= LLENAR MODAL EDITAR =================
function abrirEditar(data) {
    document.getElementById("edit_id").value = data.id_reactivo;
    document.getElementById("edit_nombre").value = data.nombre;
    document.getElementById("edit_descripcion").value = data.descripcion;
    document.getElementById("edit_unidad").value = data.unidad;
    document.getElementById("edit_stock_actual").value = data.stock_actual;
    document.getElementById("edit_stock_minimo").value = data.stock_minimo;

    new bootstrap.Modal(document.getElementById("modalEditar")).show();
}
</script>

</body>
</html>
