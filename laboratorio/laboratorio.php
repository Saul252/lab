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
    o.folio,
    o.total,
    p.nombre AS paciente,
    c.fecha_cita,
    c.hora_cita,
    c.estado AS estado_cita,
    oe.id_orden_estudio,
    e.tipo,
    e.nombre AS estudio,
    oe.estado AS estado_estudio
FROM ordenes o
JOIN pacientes p ON p.id_paciente = o.id_paciente
LEFT JOIN citas c ON c.id_cita = o.id_cita
LEFT JOIN orden_estudios oe ON oe.id_orden = o.id_orden
LEFT JOIN estudios e ON e.id_estudio = oe.id_estudio
ORDER BY o.id_orden ASC, oe.id_orden_estudio ASC
";

$res = $conexion->query($sql);

$estudios = [];
while($row = $res->fetch_assoc()){
    $estudios[] = $row;
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
                foreach($tipos as $tipo){
                    echo "<option value=\"$tipo\">$tipo</option>";
                }
                ?>
                </select>
            </div>
        </div>

        <div class="table-responsive" style="max-height:60vh; overflow:auto;">
            <table id="tablaEstudios" class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Orden</th>
                        <th>Estudio</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th style="width: 200px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($estudios as $estudio): ?>
                    <tr>
                        <td><?= $estudio['id_orden'] ?></td>
                        <td><?= htmlspecialchars($estudio['estudio']) ?></td>
                        <td><?= htmlspecialchars($estudio['tipo']) ?></td>
                        <td>
                            <?php
                        switch($estudio['estado_estudio']){
                            case 'pendiente': echo '<span class="badge bg-secondary">Pendiente</span>'; break;
                            case 'capturado': echo '<span class="badge bg-info text-dark">Capturado</span>'; break;
                            case 'validado': echo '<span class="badge bg-success">Validado</span>'; break;
                            case 'aprobado': echo '<span class="badge bg-primary">Aprobado</span>'; break;
                        }
                        ?>
                        </td>

                        <td>$<?= number_format($estudio['total'],2) ?></td>
                        <td class="text-nowrap">
                            <div style="width: 200px;" class="row g-0">
                                <!-- COLUMNA 1 -->
                                <div class="col-6 text-center">
                                    <?php if($estudio['estado_estudio']=='pendiente'): ?>
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
    $(document).ready(function() {
        var table = $('#tablaEstudios').DataTable({
            scrollY: '55vh',
            scrollCollapse: true,
            paging: true,
            order: [
                [0, 'asc']
            ],
        });

        // Filtro por Orden
        $('#filtroOrden').on('keyup change', function() {
            table.column(0).search(this.value).draw();
        });

        // Filtro por Tipo
        $('#filtroTipo').on('change', function() {
            table.column(2).search(this.value).draw();
        });
    });
    </script>

</body>

</html>