<?php
session_start();
require "../conexion.php";

/* ================= SEGURIDAD ================= */
if (!isset($_SESSION['paciente_publico'])) {
    header("Location: login.php");
    exit;
}

$id_paciente = $_SESSION['paciente_publico'];

/* ================= DATOS PACIENTE ================= */
$sqlPaciente = "
SELECT nombre, edad, sexo, fecha_nacimiento, email, medico_solicitante
FROM pacientes
WHERE id_paciente = ?
";
$stmt = $conexion->prepare($sqlPaciente);
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();

/* ================= ESTUDIOS ================= */
$sql = "
SELECT

    p.id_paciente,
    p.nombre              AS paciente,
    p.edad,
    p.sexo,
    p.fecha_nacimiento,   

    o.id_orden,
    o.folio,
    o.fecha_creacion,

   
    oe.id_orden_estudio,
    oe.estado AS estado_estudio,

    
    e.nombre AS estudio,
    e.tipo,

    
    r.valor_numerico,
    r.valor_cualitativo,
    r.unidad,
    r.url_archivo

FROM ordenes o
INNER JOIN pacientes p ON p.id_paciente = o.id_paciente
INNER JOIN orden_estudios oe ON oe.id_orden = o.id_orden
INNER JOIN estudios e ON e.id_estudio = oe.id_estudio
LEFT JOIN resultados r ON r.id_orden_estudio = oe.id_orden_estudio

WHERE o.id_paciente = ?
ORDER BY o.id_orden DESC, oe.id_orden_estudio ASC

";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$res = $stmt->get_result();

$ordenes = [];
while ($row = $res->fetch_assoc()) {
    $ordenes[$row['id_orden']]['info'] = $row;
    $ordenes[$row['id_orden']]['estudios'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resultados de Laboratorio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #f4f6f9;
}
.header-paciente {
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    color: #fff;
    border-radius: 12px;
    padding: 25px;
}
.card {
    border-radius: 12px;
}
.badge {
    font-size: .85rem;
}
</style>
</head>

<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">🧪 Resultados de Laboratorio</span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
    </div>
</nav>

<div class="container mb-5">

<!-- ================= PERFIL PACIENTE ================= -->
<div class="header-paciente mb-4 shadow">
    <h3 class="mb-1"><?= htmlspecialchars($paciente['nombre']) ?></h3>
    <div class="row mt-3">
        <div class="col-md-4">
            <strong>Edad:</strong> <?= $paciente['edad'] ?? '—' ?>
        </div>
        <div class="col-md-4">
            <strong>Sexo:</strong> <?= $paciente['sexo'] ?>
        </div>
       
        <div class="col-md-6 mt-2">
            <strong>Email:</strong> <?= htmlspecialchars($paciente['email']) ?>
        </div>
        <div class="col-md-6 mt-2">
            <strong>Médico solicitante:</strong> <?= htmlspecialchars($paciente['medico_solicitante']) ?: '—' ?>
        </div>
    </div>
</div>

<!-- ================= ORDENES ================= -->
<?php if (empty($ordenes)): ?>
<div class="alert alert-info text-center">
    No existen estudios registrados
</div>
<?php endif; ?>

<?php foreach ($ordenes as $orden): ?>
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <strong>Orden #<?= $orden['info']['id_orden'] ?></strong>
        | Folio: <?= htmlspecialchars($orden['info']['folio']) ?>
        | Fecha: <?= date('d/m/Y', strtotime($orden['info']['fecha_creacion'])) ?>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Estudio</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Resultado</th>
                    <th class="text-center">Archivo</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orden['estudios'] as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['estudio']) ?></td>
                    <td><?= htmlspecialchars($e['tipo']) ?></td>
                    <td>
                        <?php
                        switch ($e['estado_estudio']) {
                            case 'pendiente': echo '<span class="badge bg-secondary">Pendiente</span>'; break;
                            case 'capturado': echo '<span class="badge bg-info text-dark">Capturado</span>'; break;
                            case 'validado': echo '<span class="badge bg-success">Validado</span>'; break;
                            case 'aprobado': echo '<span class="badge bg-primary">Aprobado</span>'; break;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($e['valor_numerico'] !== null) {
                            echo $e['valor_numerico'].' '.$e['unidad'];
                        } elseif ($e['valor_cualitativo']) {
                            echo htmlspecialchars($e['valor_cualitativo']);
                        } else {
                            echo '<span class="text-muted">No disponible</span>';
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php if ($e['url_archivo']): ?>
                            <a href="/lab/laboratorio/paginas/interpretacionEstudio.php?id_orden_estudio=<?= htmlspecialchars($e['id_orden_estudio']) ?>"
                               target="_blank"
                               class="btn btn-sm btn-outline-primary">
                               Ver PDF
                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

</div>

</body>
</html>
