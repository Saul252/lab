<?php
session_start();
require "../conexion.php";

header('Content-Type: application/json');

/* ===============================
   VALIDAR SESIÓN
   =============================== */
if (!isset($_SESSION["usuario"])) {
    echo json_encode([
        "ok" => false,
        "error" => "Sesión no válida"
    ]);
    exit;
}

/* ===============================
   RECIBIR DATOS
   =============================== */
$id_orden   = $_POST["id_orden"] ?? null;
$monto      = $_POST["monto"] ?? null;
$metodo     = $_POST["metodo"] ?? null;
$referencia = $_POST["referencia"] ?? null;

if (!$id_orden || !$monto || !$metodo) {
    echo json_encode([
        "ok" => false,
        "error" => "Datos incompletos"
    ]);
    exit;
}

/* ===============================
   INICIAR TRANSACCIÓN
   =============================== */
$conexion->begin_transaction();

try {

    /* ===============================
       OBTENER TOTAL ORDEN
       =============================== */
    $stmt = $conexion->prepare(
        "SELECT total, estado FROM ordenes WHERE id_orden = ? FOR UPDATE"
    );
    $stmt->bind_param("i", $id_orden);
    $stmt->execute();
    $orden = $stmt->get_result()->fetch_assoc();

    if (!$orden) {
        throw new Exception("Orden no encontrada");
    }

    if ($orden["estado"] === "pagada") {
        throw new Exception("La orden ya está pagada");
    }

    /* ===============================
       INSERTAR PAGO
       =============================== */
    $stmt = $conexion->prepare(
        "INSERT INTO pagos (id_orden, metodo, monto, referencia)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "isds",
        $id_orden,
        $metodo,
        $monto,
        $referencia
    );
    $stmt->execute();

    /* ===============================
       ACTUALIZAR ESTADO ORDEN
       =============================== */
    if ($monto >= $orden["total"]) {
        $nuevo_estado = "pagada";
    } else {
        $nuevo_estado = "en_proceso"; // pago parcial
    }

    $stmt = $conexion->prepare(
        "UPDATE ordenes SET estado = ? WHERE id_orden = ?"
    );
    $stmt->bind_param("si", $nuevo_estado, $id_orden);
    $stmt->execute();

    /* ===============================
       COMMIT
       =============================== */
    $conexion->commit();

    echo json_encode([
        "ok" => true,
        "estado" => $nuevo_estado
    ]);

} catch (Exception $e) {

    $conexion->rollback();

    echo json_encode([
        "ok" => false,
        "error" => $e->getMessage()
    ]);
}
