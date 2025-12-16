<?php
session_start();
require "../../conexion.php";

// Verificar sesión

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$id_usuario = $_SESSION['id'];

// Obtener ID del reactivo a eliminar
$id_reactivo = isset($_POST['id_reactivo']) ? intval($_POST['id_reactivo']) : 0;
if ($id_reactivo <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de reactivo inválido']);
    exit;
}

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Eliminar movimientos relacionados
    $conexion->query("DELETE mr FROM movimientos_reactivos mr
                      JOIN lotes_reactivos lr ON mr.id_lote = lr.id_lote
                      WHERE lr.id_reactivo = $id_reactivo");

    // Eliminar lotes relacionados
    $conexion->query("DELETE FROM lotes_reactivos WHERE id_reactivo = $id_reactivo");

    // Eliminar reactivo
    $conexion->query("DELETE FROM reactivos WHERE id_reactivo = $id_reactivo");

    // Registrar auditoría
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $accion = "Eliminar reactivo y sus lotes";
    $tabla = "reactivos";
    $conexion->query("INSERT INTO auditoria (id_usuario, accion, tabla_afectada, id_registro_afectado, ip)
                      VALUES ($id_usuario, '$accion', '$tabla', $id_reactivo, '$ip')");

    // Commit
    $conexion->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
