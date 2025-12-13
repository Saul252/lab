<?php
session_start();
require "../../conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit;
}

$id_orden_estudio = intval($_GET['id'] ?? 0);
if ($id_orden_estudio <= 0) die("Estudio inválido");

// CONSULTA COMPLETA
$stmt = $conexion->prepare("
    SELECT 
        oe.id_orden_estudio,
        oe.estado,
        e.nombre AS estudio,
        e.unidad,
        e.tipo_resultado,
        o.folio,
        p.id_paciente,
        p.nombre AS paciente,
        p.edad,
        p.sexo
    FROM orden_estudios oe
    JOIN estudios e ON e.id_estudio = oe.id_estudio
    JOIN ordenes o ON o.id_orden = oe.id_orden
    JOIN pacientes p ON p.id_paciente = o.id_paciente
    WHERE oe.id_orden_estudio = ?
");
$stmt->bind_param("i", $id_orden_estudio);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) die("Registro no encontrado");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Capturar Resultados</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4">

<!-- PERFIL PACIENTE -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <h4><?= htmlspecialchars($data['paciente']) ?></h4>
                <small class="text-muted">Folio: <?= $data['folio'] ?></small>
            </div>
         <span class="badge bg-primary fs-6">
                <?= htmlspecialchars($data['estudio']) ?>
            </span>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-3"><strong>Edad:</strong> <?= $data['edad'] ?></div>
            <div class="col-md-3"><strong>Sexo:</strong> <?= $data['sexo'] ?></div>
            <div class="col-md-3"><strong>Unidad:</strong> <?= $data['unidad'] ?></div>
            <div class="col-md-3"><strong>Estado:</strong> <?= ucfirst($data['estado']) ?></div>
        </div>
    </div>
</div>

<!-- FORMULARIO -->
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <i class="bi bi-clipboard2-pulse"></i> Captura de Resultados
    </div>

    <div class="card-body">
        <form action="accionesResultados/guardarResultado.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="id_orden_estudio" value="<?= $id_orden_estudio ?>">

            <?php if ($data['tipo_resultado'] == 'numerico' || $data['tipo_resultado'] == 'ambos'): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Resultado Numérico</label>
                    <input type="number" step="0.01" name="valor_numerico" class="form-control">
                </div>
            <?php endif; ?>

            <?php if ($data['tipo_resultado'] == 'cualitativo' || $data['tipo_resultado'] == 'ambos'): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Resultado Cualitativo</label>
                    <select name="valor_cualitativo" class="form-select">
                        <option value="">Seleccione</option>
                        <option>Negativo</option>
                        <option>Positivo</option>
                        <option>Indeterminado</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Unidad</label>
                <input type="text" name="unidad" class="form-control" value="<?= $data['unidad'] ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Observaciones técnicas</label>
                <textarea name="observaciones" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Interpretación clínica</label>
                <textarea name="interpretacion" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">PDF del estudio</label>
                <input type="file" name="pdf" class="form-control" accept="application/pdf">
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="history.back()">
    Volver
</button>

                <button class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Guardar Resultados
                </button>
            </div>

        </form>
    </div>
</div>

</div>
</body>
</html>
