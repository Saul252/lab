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

    <?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'Estudios'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>
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