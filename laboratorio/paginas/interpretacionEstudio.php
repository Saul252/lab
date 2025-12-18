<?php
session_start();



require "../../conexion.php";

$id_orden_estudio = intval($_GET['id_orden_estudio'] ?? 0);
if ($id_orden_estudio <= 0) {
    die("Estudio inválido");
}

$stmt = $conexion->prepare("
    SELECT 
        oe.id_orden_estudio,
        oe.estado,

        o.folio,
        o.fecha_creacion,

        p.nombre AS paciente,
        p.edad,
        p.sexo,

        est.nombre AS estudio,
        est.unidad,
        est.tipo_resultado,
        est.rango_hombre_min,
        est.rango_hombre_max,
        est.rango_mujer_min,
        est.rango_mujer_max,

        r.valor_numerico,
        r.valor_cualitativo,
        r.observaciones,
        r.interpretacion

    FROM orden_estudios oe
    JOIN ordenes o ON o.id_orden = oe.id_orden
    JOIN pacientes p ON p.id_paciente = o.id_paciente
    JOIN estudios est ON est.id_estudio = oe.id_estudio
    LEFT JOIN resultados r ON r.id_orden_estudio = oe.id_orden_estudio
    WHERE oe.id_orden_estudio = ?
");
$stmt->bind_param("i", $id_orden_estudio);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Resultado no encontrado");
}

/* ================= INTERPRETACIÓN AUTOMÁTICA ================= */
function interpretar($valor, $min, $max) {
    if ($valor === null) return "SIN RESULTADO";
    if ($min !== null && $valor < $min) return "BAJO";
    if ($max !== null && $valor > $max) return "ALTO";
    return "NORMAL";
}

if ($data['tipo_resultado'] !== 'cualitativo') {
    if ($data['sexo'] === 'H') {
        $interpretacion_auto = interpretar(
            $data['valor_numerico'],
            $data['rango_hombre_min'],
            $data['rango_hombre_max']
        );
    } else {
        $interpretacion_auto = interpretar(
            $data['valor_numerico'],
            $data['rango_mujer_min'],
            $data['rango_mujer_max']
        );
    }
} else {
    $interpretacion_auto = $data['valor_cualitativo'] ?? '—';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resultado de Estudio</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="/lab/css/consultarResultado.css">

</head>

<body>

<div class="container my-4">

<div class="card p-4">

<!-- ENCABEZADO -->
<div class="lab-header mb-4">
    <h5>LABORATORIO CLÍNICO</h5>
    <div class="subtle">Reporte individual de resultados</div>
</div>

<!-- DATOS -->
<div class="row mb-4">
    <div class="col-md-8">
        <div><strong>Paciente:</strong> <?= htmlspecialchars($data['paciente']) ?></div>
        <div><strong>Edad:</strong> <?= $data['edad'] ?> años</div>
        <div><strong>Sexo:</strong> <?= $data['sexo'] ?></div>
    </div>
    <div class="col-md-4 text-end subtle">
        <div><strong>Folio:</strong> <?= $data['folio'] ?></div>
        <div><?= date('d/m/Y', strtotime($data['fecha_creacion'])) ?></div>
    </div>
</div>

<!-- TABLA RESULTADO -->
<table class="table align-middle">
<thead>
<tr>
    <th>Estudio</th>
    <th>Resultado</th>
    <th>Unidad</th>
    <th>Referencia</th>
    <th>Interpretación</th>
</tr>
</thead>
<tbody>
<tr>
<td><?= htmlspecialchars($data['estudio']) ?></td>
<td>
    <?= $data['tipo_resultado'] === 'cualitativo'
        ? htmlspecialchars($data['valor_cualitativo'])
        : $data['valor_numerico'] ?>
</td>
<td><?= $data['unidad'] ?></td>
<td>
<?php if ($data['tipo_resultado'] !== 'cualitativo'): ?>
    <?= $data['sexo'] === 'H'
        ? "{$data['rango_hombre_min']} – {$data['rango_hombre_max']}"
        : "{$data['rango_mujer_min']} – {$data['rango_mujer_max']}" ?>
<?php else: ?>—<?php endif; ?>
</td>
<td>
<?php
$badge = match($interpretacion_auto) {
    'NORMAL' => 'badge-normal',
    'ALTO' => 'badge-alto',
    'BAJO' => 'badge-bajo',
    default => 'badge-secondary'
};
?>
<span class="badge-interpretacion <?= $badge ?>">
    <?= $interpretacion_auto ?>
</span>
</td>
</tr>
</tbody>
</table>

<!-- OBSERVACIONES -->
<?php if (!empty($data['observaciones'])): ?>
<div class="mt-4">
    <div class="section-title">Observaciones técnicas</div>
    <div class="subtle"><?= nl2br(htmlspecialchars($data['observaciones'])) ?></div>
</div>
<?php endif; ?>

<!-- INTERPRETACIÓN -->
<?php if (!empty($data['interpretacion'])): ?>
<div class="mt-3">
    <div class="section-title">Interpretación clínica</div>
    <div><?= nl2br(htmlspecialchars($data['interpretacion'])) ?></div>
</div>
<?php endif; ?>

<!-- FOOTER -->
<div class="text-end mt-4 no-print">
    <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-4">
        Imprimir
    </button>
</div>

</div>
</div>

</body>
</html>
