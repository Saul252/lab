<?php
session_start();
require "../../conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id_orden_estudio = intval($_GET['id_orden_estudio'] ?? 0);
if ($id_orden_estudio <= 0) die("Estudio inválido");

// Traer datos del estudio y resultado existente
$stmt = $conexion->prepare("
    SELECT 
        oe.id_orden_estudio, oe.estado,
        o.folio, o.fecha_creacion,
        p.nombre AS paciente, p.edad, p.sexo,
        est.nombre AS estudio, est.unidad, est.tipo_resultado,
        est.rango_hombre_min, est.rango_hombre_max,
        est.rango_mujer_min, est.rango_mujer_max,
        r.id_resultado, r.valor_numerico, r.valor_cualitativo, r.unidad AS unidad_resultado,
        r.observaciones, r.interpretacion, r.url_archivo
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

if (!$data) die("Resultado no encontrado");

$mensaje = "";

// =================== PROCESAR FORMULARIO ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor_numerico    = $_POST['valor_numerico'] ?: null;
    $valor_cualitativo = $_POST['valor_cualitativo'] ?: null;
    $unidad            = $_POST['unidad'] ?: null;
    $observaciones     = $_POST['observaciones'] ?: null;
    $interpretacion    = $_POST['interpretacion'] ?: null;
    $id_usuario        = $_SESSION['id_usuario'];

    $pdf_path = $data['url_archivo'];
    $directorio = __DIR__ . "/../../../pacientes/resultados-laboratorio/";
    if (!is_dir($directorio)) mkdir($directorio, 0755, true);

    // Subir PDF si hay uno nuevo
    if (!empty($_FILES['pdf']['name'])) {
        $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') die("Solo se permiten archivos PDF");

        $nombreArchivo = "RES-{$id_orden_estudio}-" . date("YmdHis") . "-" . bin2hex(random_bytes(3)) . ".pdf";
        $rutaFinal = $directorio . $nombreArchivo;

        if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $rutaFinal)) {
            die("Error al subir el archivo. Verifica permisos.");
        }

        // Borrar PDF anterior si existe
        if ($pdf_path && file_exists($directorio . basename($pdf_path))) unlink($directorio . basename($pdf_path));

        $pdf_path = "resultados/" . $nombreArchivo;
    }

    if ($data['id_resultado']) {
        // UPDATE
        $stmt_upd = $conexion->prepare("
            UPDATE resultados
            SET valor_numerico=?, valor_cualitativo=?, unidad=?, observaciones=?, interpretacion=?, capturado_por=?, url_archivo=?, fecha_captura=NOW()
            WHERE id_resultado=?
        ");
        $stmt_upd->bind_param("dssssisi", $valor_numerico, $valor_cualitativo, $unidad, $observaciones, $interpretacion, $id_usuario, $pdf_path, $data['id_resultado']);
        $stmt_upd->execute();
        $stmt_upd->close();
    } else {
        // INSERT
        $stmt_ins = $conexion->prepare("
            INSERT INTO resultados (id_orden_estudio, valor_numerico, valor_cualitativo, unidad, observaciones, interpretacion, capturado_por, url_archivo, fecha_captura)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt_ins->bind_param("idssssis", $id_orden_estudio, $valor_numerico, $valor_cualitativo, $unidad, $observaciones, $interpretacion, $id_usuario, $pdf_path);
        $stmt_ins->execute();
        $stmt_ins->close();
    }

    $conexion->query("UPDATE orden_estudios SET estado = 'capturado' WHERE id_orden_estudio = $id_orden_estudio");

    $mensaje = "Resultado actualizado correctamente.";
    $data['valor_numerico'] = $valor_numerico;
    $data['valor_cualitativo'] = $valor_cualitativo;
    $data['unidad_resultado'] = $unidad;
    $data['observaciones'] = $observaciones;
    $data['interpretacion'] = $interpretacion;
    $data['url_archivo'] = $pdf_path;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Resultado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-header-custom {
            background-color: #198754;
            color: white;
            font-weight: bold;
        }

        .badge-resultado {
            font-size: 0.9rem;
            padding: 0.5em 0.8em;
            border-radius: 0.5rem;
        }

        .badge-normal {
            background-color: #198754;
            color: white;
        }

        .badge-alto {
            background-color: #dc3545;
            color: white;
        }

        .badge-bajo {
            background-color: #ffc107;
            color: black;
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>

    <div class="container my-4">
        <h2 class="mb-4">Editar Resultado</h2>
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- TARJETA PACIENTE -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header card-header-custom">Paciente</div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($data['paciente']) ?></p>
                        <p><strong>Edad:</strong> <?= $data['edad'] ?> años</p>
                        <p><strong>Sexo:</strong> <?= $data['sexo'] ?></p>
                        <p><strong>Folio:</strong> <?= $data['folio'] ?></p>
                        <p><strong>Fecha creación:</strong> <?= date('d/m/Y', strtotime($data['fecha_creacion'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- TARJETA ESTUDIO -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header card-header-custom">Estudio</div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($data['estudio']) ?></p>
                        <p><strong>Unidad:</strong> <?= htmlspecialchars($data['unidad_resultado'] ?: $data['unidad']) ?></p>
                        <p><strong>Estado:</strong> <?= htmlspecialchars($data['estado']) ?></p>
                    </div>
                </div>
            </div>

        </div>

        <form method="POST" enctype="multipart/form-data" class="mt-4">

            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Valor Numérico</label>
                    <input type="number" step="0.01" name="valor_numerico" class="form-control" value="<?= htmlspecialchars($data['valor_numerico']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Valor Cualitativo</label>
                    <input type="text" name="valor_cualitativo" class="form-control" value="<?= htmlspecialchars($data['valor_cualitativo']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unidad</label>
                    <input type="text" name="unidad" class="form-control" value="<?= htmlspecialchars($data['unidad_resultado']) ?>">
                </div>

            </div>

            <div class="mb-3 mt-3">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control"><?= htmlspecialchars($data['observaciones']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Interpretación</label>
                <textarea name="interpretacion" class="form-control"><?= htmlspecialchars($data['interpretacion']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Archivo PDF</label>
                <?php if ($data['url_archivo'] && file_exists(__DIR__ . "/../../../pacientes/resultados-laboratorio/" . basename($data['url_archivo']))): ?>
                    <div class="mb-2">
                        <a href="/lab/pacientes/resultados-laboratorio/<?= basename($data['url_archivo']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">Ver PDF actual</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="pdf" class="form-control" accept="application/pdf">
            </div>

            <button type="submit" class="btn btn-success">Actualizar Resultado</button>
            <button onclick="history.back()" class="btn btn-danger ">
                ← Regresar
            </button>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>