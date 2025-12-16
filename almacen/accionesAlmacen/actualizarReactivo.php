<?php
session_start();
require "../../conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['usuario'])){
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_reactivo = $_POST['id_reactivo'] ?? null;
if(!$id_reactivo){
    echo json_encode(['success' => false, 'message' => 'ID de reactivo no proporcionado']);
    exit;
}

// ================== Actualizar datos generales del reactivo ==================
$nombre = $conexion->real_escape_string($_POST['nombre']);
$descripcion = $conexion->real_escape_string($_POST['descripcion'] ?? '');
$unidad = $conexion->real_escape_string($_POST['unidad'] ?? '');
$stock_minimo = intval($_POST['stock_minimo'] ?? 0);

$updateReactivo = $conexion->query("
    UPDATE reactivos 
    SET nombre='$nombre',
        descripcion='$descripcion',
        unidad='$unidad',
        stock_minimo=$stock_minimo
    WHERE id_reactivo=$id_reactivo
");

if(!$updateReactivo){
    echo json_encode(['success' => false, 'message' => 'Error al actualizar reactivo']);
    exit;
}

// ================== Actualizar lotes ==================
$lotes_db = $conexion->query("SELECT id_lote FROM lotes_reactivos WHERE id_reactivo=$id_reactivo");
$lotes_actuales = [];
while($l = $lotes_db->fetch_assoc()){
    $lotes_actuales[] = $l['id_lote'];
}

foreach($_POST as $key => $value){
    if(strpos($key, 'numero_lote_') === 0){
        $id_lote = intval(str_replace('numero_lote_', '', $key));
        $numero_lote = $conexion->real_escape_string($value);
        $fecha_caducidad = $conexion->real_escape_string($_POST['fecha_caducidad_'.$id_lote] ?? null);
        $cantidad = intval($_POST['cantidad_'.$id_lote] ?? 0);

        // Actualizar lote
        $conexion->query("
            UPDATE lotes_reactivos SET
                numero_lote='$numero_lote',
                fecha_caducidad='$fecha_caducidad',
                cantidad=$cantidad
            WHERE id_lote=$id_lote
        ");
    }
}

// ================== Actualizar stock total ==================
// El stock total del reactivo será la suma de todos los lotes
$sum_stock = $conexion->query("SELECT SUM(cantidad) as total_stock FROM lotes_reactivos WHERE id_reactivo=$id_reactivo")->fetch_assoc();
$total_stock = intval($sum_stock['total_stock']);
$conexion->query("UPDATE reactivos SET stock_actual=$total_stock WHERE id_reactivo=$id_reactivo");

// ================== Respuesta ==================
echo json_encode(['success' => true]);
