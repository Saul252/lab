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
   <?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'Caja'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>
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