<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../login.php");
    exit();
}

require "../../conexion.php";

$id_pago = $_GET['id_pago'] ?? 0;

$sql = "
SELECT 
    p.id_pago,
    p.id_orden,
    p.monto,
    p.metodo,
    p.referencia,
    o.folio,
    pa.nombre AS paciente
FROM pagos p
INNER JOIN ordenes o ON o.id_orden = p.id_orden
INNER JOIN pacientes pa ON pa.id_paciente = o.id_paciente
WHERE p.id_pago = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_pago);
$stmt->execute();
$pago = $stmt->get_result()->fetch_assoc();

if (!$pago) {
    die("Pago no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning">
            <h5 class="mb-0">✏️ Editar Pago</h5>
        </div>

        <form method="POST" action="/lab/caja/accionesCaja/editarCobro.php">
            <div class="card-body">

                <input type="hidden" name="id_pago" value="<?= $pago['id_pago'] ?>">

                <p><strong>Folio:</strong> <?= $pago['folio'] ?></p>
                <p><strong>Paciente:</strong> <?= $pago['paciente'] ?></p>

                <div class="mb-3">
                    <label class="form-label">Monto</label>
                    <input type="number" step="0.01" name="monto"
                           value="<?= $pago['monto'] ?>"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Método</label>
                    <select name="metodo" class="form-select" required>
                        <option value="efectivo" <?= $pago['metodo']=='efectivo'?'selected':'' ?>>Efectivo</option>
                        <option value="tarjeta" <?= $pago['metodo']=='tarjeta'?'selected':'' ?>>Tarjeta</option>
                        <option value="transferencia" <?= $pago['metodo']=='transferencia'?'selected':'' ?>>Transferencia</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Referencia</label>
                    <input type="text" name="referencia"
                           value="<?= $pago['referencia'] ?>"
                           class="form-control">
                </div>

            </div>

            <div class="card-footer text-end">
                <a href="caja.php" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-success">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
