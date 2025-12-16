<?php
session_start();
require "../../conexion.php";

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $unidad = $_POST['unidad'];

    // Datos del lote inicial
    $numero_lote = $_POST['numero_lote'] ?? null;
    $fecha_caducidad = $_POST['fecha_caducidad'] ?? null;
    $cantidad = $_POST['cantidad'] ?? 0;

    // 1️⃣ Guardar reactivo
    $stmt = $conexion->prepare("INSERT INTO reactivos (nombre, descripcion, unidad) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $descripcion, $unidad);

    if($stmt->execute()){
        $id_reactivo = $stmt->insert_id;

        // 2️⃣ Guardar lote inicial si hay cantidad
        if($cantidad > 0 && $numero_lote) {
            $stmt2 = $conexion->prepare("INSERT INTO lotes_reactivos (id_reactivo, numero_lote, fecha_caducidad, cantidad) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("issi", $id_reactivo, $numero_lote, $fecha_caducidad, $cantidad);
            $stmt2->execute();
            $id_lote = $stmt2->insert_id;

            // Registrar movimiento de entrada
            $tipo = 'entrada';
            $descripcion_mov = 'Lote inicial al crear reactivo';
            $stmt3 = $conexion->prepare("INSERT INTO movimientos_reactivos (id_lote, tipo, cantidad, descripcion) VALUES (?, ?, ?, ?)");
            $stmt3->bind_param("isis", $id_lote, $tipo, $cantidad, $descripcion_mov);
            $stmt3->execute();
            $stmt3->close();

            $stmt2->close();
        }

        echo json_encode(['success'=>true, 'id_reactivo'=>$id_reactivo]);
    } else {
        echo json_encode(['success'=>false, 'message'=>$stmt->error]);
    }

    $stmt->close();
}
?>
