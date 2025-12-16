<?php

session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

require 'conexion.php';

$busqueda = $_GET['busqueda'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$ordenColumna = $_GET['ordenColumna'] ?? 'fecha_creacion';
$ordenTipo = $_GET['ordenTipo'] ?? 'ASC';

$sql = "SELECT o.id_orden, o.folio, o.fecha_creacion, p.nombre AS paciente,
        GROUP_CONCAT(e.nombre SEPARATOR ', ') AS estudios,
        GROUP_CONCAT(oe.estado SEPARATOR ', ') AS estados_estudio
        FROM ordenes o
        JOIN pacientes p ON o.id_paciente = p.id_paciente
        JOIN orden_estudios oe ON oe.id_orden = o.id_orden
        JOIN estudios e ON e.id_estudio = oe.id_estudio
        WHERE (o.folio LIKE ? OR p.nombre LIKE ? OR e.nombre LIKE ?)";

if ($tipo != '') {
    $sql .= " AND e.tipo_resultado = ?";
}

$sql .= " GROUP BY o.id_orden ORDER BY $ordenColumna $ordenTipo";

$stmt = $conn->prepare($sql);

$busqueda_param = "%$busqueda%";

if ($tipo != '') {
    $stmt->bind_param('ssss', $busqueda_param, $busqueda_param, $busqueda_param, $tipo);
} else {
    $stmt->bind_param('sss', $busqueda_param, $busqueda_param, $busqueda_param);
}

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($data);
