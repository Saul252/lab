<?php
session_start();
require "../conexion.php";

if(!isset($_SESSION['usuario'])){
    echo json_encode(["ok"=>false,"error"=>"No autorizado"]);
    exit;
}

$id_pago = $_POST['id_pago'] ?? null;
$monto = $_POST['monto'] ?? null;
$metodo = $_POST['metodo'] ?? null;
$referencia = $_POST['referencia'] ?? null;

if(!$id_pago || !$monto || !$metodo){
    echo json_encode(["ok"=>false,"error"=>"Datos incompletos"]);
    exit;
}

$conexion->begin_transaction();

try {

    // Obtener orden del pago
    $q = $conexion->prepare("SELECT id_orden FROM pagos WHERE id_pago=?");
    $q->bind_param("i",$id_pago);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();

    if(!$res) throw new Exception("Pago no encontrado");

    $id_orden = $res['id_orden'];

    // Actualizar pago
    $u = $conexion->prepare("
        UPDATE pagos
        SET monto=?, metodo=?, referencia=?
        WHERE id_pago=?
    ");
    $u->bind_param("dssi",$monto,$metodo,$referencia,$id_pago);
    $u->execute();

    // Recalcular total pagado
    $s = $conexion->prepare("
        SELECT SUM(monto) total_pagado
        FROM pagos
        WHERE id_orden=?
    ");
    $s->bind_param("i",$id_orden);
    $s->execute();
    $total_pagado = $s->get_result()->fetch_assoc()['total_pagado'] ?? 0;

    // Total orden
    $t = $conexion->prepare("SELECT total FROM ordenes WHERE id_orden=?");
    $t->bind_param("i",$id_orden);
    $t->execute();
    $total_orden = $t->get_result()->fetch_assoc()['total'];

    // Estado orden
    $estado = ($total_pagado >= $total_orden) ? 'pagada' : 'pendiente';

    $e = $conexion->prepare("UPDATE ordenes SET estado=? WHERE id_orden=?");
    $e->bind_param("si",$estado,$id_orden);
    $e->execute();

    // Auditoría
    $a = $conexion->prepare("
        INSERT INTO auditoria
        (id_usuario, accion, tabla_afectada, id_registro_afectado, ip)
        VALUES (?,?,?,?,?)
    ");
    $accion = "Actualizó pago ID $id_pago";
    $tabla = "pagos";
    $usuario = $_SESSION['id_usuario'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $a->bind_param("issis",$usuario,$accion,$tabla,$id_pago,$ip);
    $a->execute();

    $conexion->commit();

    echo json_encode(["ok"=>true]);

} catch(Exception $e){
    $conexion->rollback();
    echo json_encode(["ok"=>false,"error"=>$e->getMessage()]);
}
