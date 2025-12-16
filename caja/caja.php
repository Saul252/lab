<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
require "../conexion.php";

/* =====================================================
   OBTENER ÓRDENES PENDIENTES
   ===================================================== */
$sql = "
    SELECT o.id_orden, o.folio, o.total, o.estado, 
           p.nombre AS paciente
    FROM ordenes o
    JOIN pacientes p ON p.id_paciente = o.id_paciente
    WHERE o.estado IN ('pendiente','en_proceso')
    ORDER BY o.fecha_creacion DESC
";
$res = $conexion->query($sql);


/* =====================================================
   CORTE DE CAJA (Opcional)
   ===================================================== */
$filtro = $_GET["filtro"] ?? "";
$valor = $_GET["valor"] ?? "";
$where = "";

if ($filtro === "dia" && $valor != "") {
    $where = "WHERE DATE(p.fecha_pago) = '$valor'";
} 
elseif ($filtro === "semana" && $valor != "") {
    $anio = substr($valor, 0, 4);
    $semana = substr($valor, 6);

    $where = "WHERE YEAR(p.fecha_pago) = '$anio' 
              AND WEEK(p.fecha_pago, 1) = '$semana'";
}
elseif ($filtro === "mes" && $valor != "") {
    $where = "WHERE DATE_FORMAT(p.fecha_pago, '%Y-%m') = '$valor'";
}

$sql_corte = "
SELECT 
    p.id_pago,
    p.id_orden,
    p.monto,
    p.metodo,
    p.referencia,
    p.fecha_pago,

    o.folio,
    o.total AS total_orden,

    pa.nombre AS paciente
FROM pagos p
INNER JOIN ordenes o ON o.id_orden = p.id_orden
INNER JOIN pacientes pa ON pa.id_paciente = o.id_paciente
$where
ORDER BY p.fecha_pago DESC
";

$res_corte = $conexion->query($sql_corte);

// Total
$sql_total = "SELECT SUM(monto) AS total FROM pagos p $where";
$res_total = $conexion->query($sql_total);
$total_corte = $res_total->fetch_assoc()["total"] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Caja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">

    <!-- SweetAlert + PDF -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
    body {
        background: #f4f5f7;
    }

    .card-custom {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .scroll-table {
        max-height: 400px;
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
    <!-- =========================================================
         Navbar
         ========================================================= -->
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
                                <a href="/lab/caja/caja.php" class="nav-link text-white active hoverbutton">
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

            <span class="navbar-brand">Caja</span>

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

        <h3 class="fw-bold mb-3">💲 Caja y Pagos</h3>

        <!-- =========================================================
         ÓRDENES PENDIENTES
         ========================================================= -->
        <div class="card-custom mb-4">
            <h5 class="mb-3">Órdenes pendientes de pago</h5>

            <div class="scroll-table">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Paciente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while($o = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?= $o['folio'] ?></td>
                            <td><?= $o['paciente'] ?></td>
                            <td>$<?= number_format($o['total'],2) ?></td>
                            <td>
                                <span class="badge 
                                <?= $o['estado']=='pendiente' ? 'bg-warning' : 'bg-info' ?>">
                                    <?= ucfirst($o['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="cobrar(this)"
                                    data-id="<?= $o['id_orden'] ?>" data-folio="<?= $o['folio'] ?>"
                                    data-paciente="<?= htmlspecialchars($o['paciente']) ?>"
                                    data-total="<?= $o['total'] ?>">
                                    Cobrar
                                </button>


                               
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>
        </div>
        <!-- =========================================================
         Modal Cobrar
         ========================================================= -->
        <div class="modal fade" id="modalCobrar" tabindex="-1">
            <div class="modal-dialog">
                <form id="formCobro" class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Cobrar orden</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id_orden" id="id_orden">

                        <p><strong>Folio:</strong> <span id="txtFolio"></span></p>
                        <p><strong>Paciente:</strong> <span id="txtPaciente"></span></p>

                        <div class="mb-3">
                            <label class="form-label">Monto</label>
                            <input type="number" step="0.01" class="form-control" name="monto" id="monto" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Método de pago</label>
                            <select class="form-select" name="metodo" required>
                                <option value="">Seleccione...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Referencia</label>
                            <input type="text" class="form-control" name="referencia">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            Registrar pago
                        </button>
                    </div>

                </form>
            </div>
        </div>



        <!-- =========================================================
         CORTE DE CAJA
         ========================================================= -->
        <div class="card-custom">

            <h5 class="mb-3">📊 Corte de Caja</h5>

            <form class="row g-3 mb-3" method="GET">

                <!-- Tipo de corte -->
                <div class="col-md-3">
                    <label class="form-label">Tipo de corte</label>
                    <select name="filtro" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="dia" <?= $filtro=="dia"?"selected":"" ?>>Por Día</option>
                        <option value="semana" <?= $filtro=="semana"?"selected":"" ?>>Por Semana</option>
                        <option value="mes" <?= $filtro=="mes"?"selected":"" ?>>Por Mes</option>
                    </select>
                </div>

                <!-- Valor del filtro -->
                

                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Aplicar</button>
                </div>
            </form>

            <!-- TOTAL -->
            <div class="alert alert-info">
                <h5 class="m-0">Total del corte:
                    <strong>$<?= number_format($total_corte,2) ?></strong>
                </h5>
            </div>

            <!-- TABLA DEL CORTE -->
            <div id="pdfCorte">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Folio</th>
                            <th>Paciente</th>
                            <th>Método</th>
                            <th>Monto</th>
                            <th>Fecha Pago</th>
                         
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = $res_corte->fetch_assoc()): ?>
                        <tr>
                            <td><?= $p['id_pago'] ?></td>
                            <td><?= $p['folio'] ?></td>
                            <td><?= $p['paciente'] ?></td>
                            <td><?= ucfirst($p['metodo']) ?></td>
                            <td>$<?= number_format($p['monto'],2) ?></td>
                            <td><?= $p['fecha_pago'] ?></td>
                            
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <button class="btn btn-danger mt-3" onclick="descargarPDF()">Descargar PDF</button>
        </div>

    </div>

    <!-- Modal Editar cobro -->
    


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- scrip descargar pdf -->
    <script>
    function descargarPDF() {
        const {
            jsPDF
        } = window.jspdf;
        let pdf = new jsPDF('p', 'pt', 'a4');
        let area = document.getElementById("pdfCorte");

        html2canvas(area).then(canvas => {
            let img = canvas.toDataURL("image/png");
            let ancho = pdf.internal.pageSize.getWidth();
            let alto = canvas.height * ancho / canvas.width;

            pdf.addImage(img, 'PNG', 0, 20, ancho, alto);
            pdf.save("corte_caja.pdf");
        });
    }
    </script>
    <!-- scrip registrar pago -->
    <script>
    function cobrar(btn) {

        const idOrden = btn.dataset.id;
        const folio = btn.dataset.folio;
        const paciente = btn.dataset.paciente;
        const total = btn.dataset.total;

        document.getElementById("id_orden").value = idOrden;
        document.getElementById("txtFolio").textContent = folio;
        document.getElementById("txtPaciente").textContent = paciente;
        document.getElementById("monto").value = total;

        new bootstrap.Modal(
            document.getElementById("modalCobrar")
        ).show();
    }


    document.getElementById("formCobro").addEventListener("submit", function(e) {
        e.preventDefault();

        let datos = new FormData(this);

        Swal.fire({
            title: 'Confirmar cobro',
            text: '¿Deseas registrar este pago?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cobrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch("/lab/caja/registrarPago.php", {
                    method: "POST",
                    body: datos
                })
                .then(r => r.json())
                .then(res => {

                    if (res.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pago registrado',
                            text: 'El pago se guardó correctamente'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.error
                        });
                    }

                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión con el servidor'
                    });
                });

        });
    });
    </script>


</body>

</html>