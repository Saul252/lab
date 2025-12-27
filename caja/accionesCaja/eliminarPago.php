<?php
session_start();
require "../../conexion.php";

header('Content-Type: application/json');

if (!isset($_SESSION["id"])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida']);
    exit();
}

$id_pago    = $_POST['id_pago'] ?? 0;
$id_usuario = $_SESSION["id"];

/* ===============================
   OBTENER DATOS DEL PAGO
   =============================== */
$sql = "SELECT id_orden, monto, metodo FROM pagos WHERE id_pago = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_pago);
$stmt->execute();
$pago = $stmt->get_result()->fetch_assoc();

if (!$pago) {
    echo json_encode(['ok' => false, 'error' => 'Pago no encontrado']);
    exit();
}

$id_orden = $pago['id_orden'];

/* ===============================
   TRANSACCIÓN SEGURA
   =============================== */
$conexion->begin_transaction();

try {

    /* 1️⃣ Eliminar pago */
    $sqlDel = "DELETE FROM pagos WHERE id_pago = ?";
    $stmtDel = $conexion->prepare($sqlDel);
    $stmtDel->bind_param("i", $id_pago);
    $stmtDel->execute();

    /* 2️⃣ Regresar orden a pendiente */
    $sqlOrden = "
        UPDATE ordenes 
        SET estado = 'pendiente'
        WHERE id_orden = ?
    ";
    $stmtOrd = $conexion->prepare($sqlOrden);
    $stmtOrd->bind_param("i", $id_orden);
    $stmtOrd->execute();

    /* 3️⃣ Auditoría */
    $accion = "Eliminó pago ID $id_pago de la orden $id_orden";
    $tabla  = "pagos";

    $sqlAudit = "
        INSERT INTO auditoria
        (id_usuario, accion, tabla_afectada, id_registro_afectado)
        VALUES (?,?,?,?)
    ";

    $stmtAud = $conexion->prepare($sqlAudit);
    $stmtAud->bind_param("issi", $id_usuario, $accion, $tabla, $id_pago);
    $stmtAud->execute();

    $conexion->commit();

    echo json_encode(['ok' => true]);

} catch (Exception $e) {

    $conexion->rollback();
    echo json_encode(['ok' => false, 'error' => 'Error al eliminar el pago']);
}
