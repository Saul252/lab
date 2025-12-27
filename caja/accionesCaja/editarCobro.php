<?php
session_start();
require "../../conexion.php";

$id_pago    = $_POST['id_pago'];
$monto      = $_POST['monto'];
$metodo     = $_POST['metodo'];
$referencia = $_POST['referencia'];
$id_usuario = $_SESSION["id"];

/* ==========================
   ACTUALIZAR PAGO
   ========================== */
$sql = "
UPDATE pagos
SET monto = ?, metodo = ?, referencia = ?
WHERE id_pago = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("dssi", $monto, $metodo, $referencia, $id_pago);
$ok = $stmt->execute();

/* ==========================
   AUDITORÍA
   ========================== */
if ($ok) {
    $accion = "Editó pago ID $id_pago (método: $metodo, monto: $monto)";
    $tabla  = "pagos";

    $sqlAudit = "
    INSERT INTO auditoria (id_usuario, accion, tabla_afectada, id_registro_afectado)
    VALUES (?,?,?,?)
    ";

    $stmt2 = $conexion->prepare($sqlAudit);
    $stmt2->bind_param("issi", $id_usuario, $accion, $tabla, $id_pago);
    $stmt2->execute();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizando pago...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
<?php if ($ok): ?>
Swal.fire({
    icon: 'success',
    title: 'Pago actualizado',
    text: 'Los cambios se guardaron correctamente',
    confirmButtonText: 'Aceptar'
}).then(() => {
    window.location.href = '/lab/caja/caja.php';
});
<?php else: ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'No se pudo actualizar el pago',
    confirmButtonText: 'Volver'
}).then(() => {
    window.history.back();
});
<?php endif; ?>
</script>

</body>
</html>
