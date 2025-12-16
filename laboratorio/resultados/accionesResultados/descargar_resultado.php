<?php
require "../../../conexion.php";

if (!isset($_POST['id_orden_estudio'])) {
    http_response_code(400);
    exit("Solicitud inválida");
}

$idOrdenEstudio = intval($_POST['id_orden_estudio']);

// 1️⃣ Buscar el archivo asociado
$stmt = $conexion->prepare("
    SELECT url_archivo
    FROM resultados
    WHERE id_orden_estudio = ?
    LIMIT 1
");
$stmt->bind_param("i", $idOrdenEstudio);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    exit("No existe resultado para esta orden");
}

$row = $res->fetch_assoc();
$archivo = basename($row['url_archivo']); // seguridad

// 2️⃣ Ruta física REAL (tu ruta exacta)
$rutaFisica = "/opt/lampp/htdocs/lab/pacientes/resultados-laboratorio/" . $archivo;

// 3️⃣ Validar archivo
if (!file_exists($rutaFisica)) {
    http_response_code(404);
    exit("Archivo no encontrado en el servidor");
}

// 4️⃣ Forzar descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$archivo.'"');
header('Content-Length: ' . filesize($rutaFisica));

readfile($rutaFisica);
exit;
