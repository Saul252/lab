<?php
require "../../conexion.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false, 'msg'=>'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
if ($nombre === '') {
    echo json_encode(['ok'=>false, 'msg'=>'El nombre es obligatorio']);
    exit;
}

$stmt = $conexion->prepare("
    INSERT INTO pacientes
    (nombre, edad, sexo, domicilio, telefono, email, medico_solicitante)
    VALUES (?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "sisssss",
    $nombre,
    $_POST['edad'],
    $_POST['sexo'],
    $_POST['domicilio'],
    $_POST['telefono'],
    $_POST['email'],
    $_POST['medico_solicitante']
);

if ($stmt->execute()) {
    echo json_encode([
        'ok'     => true,
        'id'     => $stmt->insert_id,
        'nombre' => $nombre
    ]);
} else {
    echo json_encode(['ok'=>false,'msg'=>'Error al guardar']);
}
