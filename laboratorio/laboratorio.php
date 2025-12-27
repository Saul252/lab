<?php
session_start();
require "../conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$sql = "
SELECT 
    o.id_orden,
    o.total,
    oe.id_orden_estudio,
    oe.estado AS estado_estudio,
    e.tipo,
    e.nombre AS estudio
FROM ordenes o
JOIN orden_estudios oe ON oe.id_orden = o.id_orden
JOIN estudios e ON e.id_estudio = oe.id_estudio
ORDER BY o.id_orden ASC, oe.id_orden_estudio ASC
";


$res = $conexion->query($sql);

$estudios = [];
while ($row = $res->fetch_assoc()) {
    $estudios[] = $row;
}
// ============================
// PETICIÓN AJAX (solo tabla)
// ============================
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    foreach ($estudios as $estudio) {
        ?>
        <tr>
            <td><?= $estudio['id_orden'] ?></td>
            <td><?= htmlspecialchars($estudio['estudio']) ?></td>
            <td><?= htmlspecialchars($estudio['tipo']) ?></td>
            <td>
                <?php
                switch ($estudio['estado_estudio']) {
                    case 'pendiente': echo '<span class="badge bg-secondary">Pendiente</span>'; break;
                    case 'capturado': echo '<span class="badge bg-info text-dark">Capturado</span>'; break;
                    case 'validado':  echo '<span class="badge bg-success">Validado</span>'; break;
                    case 'aprobado':  echo '<span class="badge bg-primary">Aprobado</span>'; break;
                }
                ?>
            </td>
            <td>$<?= number_format($estudio['total'], 2) ?></td>
            <td class="text-nowrap">
                <div class="row g-0" style="width:200px">
                    <div class="col-6 text-center">
                        <?php if ($estudio['estado_estudio'] == 'pendiente'): ?>
                            <a class="btn btn-sm btn-success"
                               href="/lab/laboratorio/resultados/capturarResultados.php?id=<?= $estudio['id_orden_estudio'] ?>">Realizar</a>
                        <?php else: ?>
                            <a class="btn btn-sm btn-warning"
                               href="/lab/laboratorio/paginas/editarResultado.php?id_orden_estudio=<?= $estudio['id_orden_estudio'] ?>">Editar</a>
                        <?php endif; ?>
                    </div>
                    <div class="col-6 text-center">
                        <?php if ($estudio['estado_estudio'] == 'pendiente'): ?>
                            <a class="btn btn-secondary btn-sm disabled">Resultados</a>
                        <?php else: ?>
                            <a class="btn btn-primary btn-sm"
                               href="/lab/laboratorio/paginas/interpretacionEstudio.php?id_orden_estudio=<?= $estudio['id_orden_estudio'] ?>">Resultados</a>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
        <?php
    }
    exit; // ⛔ MUY IMPORTANTE
}

?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'laboratorio'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Laboratorio - Órdenes de Estudios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.1/css/fixedHeader.bootstrap5.min.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">
    <link rel="stylesheet" href="/lab/css/style.css">
</head>

<body class="bg-light">

    <style>
        #tablaEstudios thead,
        #tablaEstudios tbody {
            display: block;
        }

        #tablaEstudios tbody {
            max-height: 50vh;
            overflow-y: auto;
        }

        #tablaEstudios thead tr,
        #tablaEstudios tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .sortable::after {
            content: ' ⇅';
            font-size: 0.75em;
            opacity: 0.6;
        }
    </style>


    <div class="container mt-4">
        <h4 class="mb-3">🧾 Órdenes / Estudios</h4>

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" id="filtroOrden" class="form-control" placeholder="Filtrar por Orden">
            </div>
            
            <div class="col-md-3">
                <select id="filtroTipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <?php
                    $tipos = array_unique(array_column($estudios, 'tipo'));
                    sort($tipos);
                    foreach ($tipos as $tipo) {
                        echo "<option value=\"$tipo\">$tipo</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
    <select id="filtroEstado" class="form-select">
        <option value="">Todos los estados</option>
        <option value="pendiente">Pendiente</option>
        <option value="capturado">Capturado</option>
       
    </select>
</div>

        </div>

        <div class="table-responsive" style="max-height:60vh; overflow:auto;">
            <table id="tablaEstudios" class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th data-col="0" data-type="number" class="sortable">Orden</th>
                        <th>Estudio</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th style="width: 200px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <?php foreach ($estudios as $estudio): ?>
                        <tr>
                            <td><?= $estudio['id_orden'] ?></td>
                            <td><?= htmlspecialchars($estudio['estudio']) ?></td>
                            <td><?= htmlspecialchars($estudio['tipo']) ?></td>
                            <td>
                                <?php
                                switch ($estudio['estado_estudio']) {
                                    case 'pendiente':
                                        echo '<span class="badge bg-secondary">Pendiente</span>';
                                        break;
                                    case 'capturado':
                                        echo '<span class="badge bg-info text-dark">Capturado</span>';
                                        break;
                                    case 'validado':
                                        echo '<span class="badge bg-success">Validado</span>';
                                        break;
                                    case 'aprobado':
                                        echo '<span class="badge bg-primary">Aprobado</span>';
                                        break;
                                }
                                ?>
                            </td>
                            <td>$<?= number_format($estudio['total'], 2) ?></td>
                            <td class="text-nowrap">
                                <div style="width: 200px;" class="row g-0">
                                    <!-- COLUMNA 1 -->
                                    <div class="col-6 text-center">
                                        <?php if ($estudio['estado_estudio'] == 'pendiente'): ?>
                                            <a class="btn btn-sm btn-success"
                                                href="/lab/laboratorio/resultados/capturarResultados.php?id=<?= $estudio['id_orden_estudio'] ?>">Realizar</a>
                                        <?php else: ?>
                                            <a class="btn btn-sm btn-warning"
                                                href="/lab/laboratorio/paginas/editarResultado.php?id_orden_estudio=<?= $estudio['id_orden_estudio'] ?>">Editar</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-6 text-center">
                                        <?php if ($estudio['estado_estudio'] == 'pendiente'): ?>
                                            <a class="btn btn-secondary btn-sm disabled"
                                                href="#"
                                                tabindex="-1"
                                                aria-disabled="true"
                                                onclick="return false;">
                                                Resultados
                                            </a>
                                        <?php else: ?>
                                            <a class="btn btn-primary btn-sm"
                                                href="/lab/laboratorio/paginas/interpretacionEstudio.php?id_orden_estudio=<?= $estudio['id_orden_estudio']; ?>">
                                                Resultados
                                            </a>
                                        <?php endif; ?>


                                    </div>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
   
  <script>
const filtroOrden  = document.getElementById('filtroOrden');
const filtroTipo   = document.getElementById('filtroTipo');
const filtroEstado = document.getElementById('filtroEstado');
const filas        = document.querySelectorAll('#tablaBody tr');
const tablaBody    = document.getElementById('tablaBody');

let ordenAsc = true;

// 🔎 FILTRAR
function filtrarTabla() {
    const busqueda = filtroOrden.value.toLowerCase();
    const tipo     = filtroTipo.value.toLowerCase();
    const estado   = filtroEstado.value.toLowerCase();

    filas.forEach(fila => {
        const colOrden   = fila.children[0].innerText.toLowerCase();
        const colEstudio = fila.children[1].innerText.toLowerCase();
        const colTipo    = fila.children[2].innerText.toLowerCase();
        const colEstado  = fila.children[3].innerText.toLowerCase();

        const visible =
            (colOrden.includes(busqueda) || colEstudio.includes(busqueda)) &&
            (tipo === '' || colTipo === tipo) &&
            (estado === '' || colEstado.includes(estado));

        fila.style.display = visible ? '' : 'none';
    });
}

// 🔃 ORDENAR SOLO POR ORDEN
document.querySelector('.sortable').addEventListener('click', () => {
    const rows = Array.from(tablaBody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const A = parseInt(a.children[0].innerText) || 0;
        const B = parseInt(b.children[0].innerText) || 0;
        return ordenAsc ? A - B : B - A;
    });

    ordenAsc = !ordenAsc;
    rows.forEach(r => tablaBody.appendChild(r));
});

// Eventos
filtroOrden.addEventListener('input', filtrarTabla);
filtroTipo.addEventListener('change', filtrarTabla);
filtroEstado.addEventListener('change', filtrarTabla);

// 🔄 refresco automático
//setInterval(() => location.reload(), 20000);
</script>


<script>
function actualizarTabla() {
    fetch(window.location.pathname + '?ajax=1')
        .then(res => res.text())
        .then(html => {
            document.getElementById('tablaBody').innerHTML = html;
            filtrarTabla(); // reaplica filtros actuales
        })
        .catch(err => console.error('Error al actualizar tabla:', err));
}

// ⏱️ refresco cada 20 segundos SOLO de la tabla
setInterval(actualizarTabla, 20000);
</script>

</body>

</html>