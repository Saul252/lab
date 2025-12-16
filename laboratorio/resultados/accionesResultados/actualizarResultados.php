<?php
session_start();
require "../../../conexion.php";

if (!isset($_SESSION['usuario'])) {
    die("Sesión no válida");
}

/* =====================================================
   FUNCIÓN PARA NOMBRE ÚNICO DE PDF
   ===================================================== */
function generarNombrePDF($id_orden_estudio) {
    return "RES-" .
           $id_orden_estudio . "-" .
           date("YmdHis") . "-" .
           bin2hex(random_bytes(3)) .
           ".pdf";
}

/* =====================================================
   VALIDAR ID
   ===================================================== */
$id_orden_estudio = intval($_POST['id_orden_estudio'] ?? 0);
if ($id_orden_estudio <= 0) {
    die("ID inválido");
}

/* =====================================================
   DATOS DEL FORMULARIO
   ===================================================== */
$valor_numerico    = ($_POST['valor_numerico'] !== '') ? $_POST['valor_numerico'] : null;
$valor_cualitativo = $_POST['valor_cualitativo'] ?? null;
$unidad            = $_POST['unidad'] ?? null;
$observaciones     = $_POST['observaciones'] ?? null;
$interpretacion    = $_POST['interpretacion'] ?? null;
$id_usuario = $_SESSION['id_usuario'];

/* =====================================================
   MANEJO DEL PDF
   ===================================================== */
$pdf_path = null;
$nombreArchivo = null;

// Carpeta donde se guardan los PDFs
$directorio = __DIR__ . "/../../../pacientes/resultados-laboratorio/";
if (!is_dir($directorio)) mkdir($directorio, 0755, true);

// Revisar si ya existe resultado para este id_orden_estudio
$stmt_check = $conexion->prepare("SELECT id_resultado, url_archivo FROM resultados WHERE id_orden_estudio = ?");
$stmt_check->bind_param("i", $id_orden_estudio);
$stmt_check->execute();
$res_check = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

$id_resultado = $res_check['id_resultado'] ?? null;
$ruta_existente = $res_check['url_archivo'] ?? null;

// Procesar archivo PDF si se sube
if (!empty($_FILES['pdf']['name'])) {
    $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') die("Solo se permiten archivos PDF");

    $nombreArchivo = generarNombrePDF($id_orden_estudio);
    $rutaFinal = $directorio . $nombreArchivo;

    if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $rutaFinal)) {
        die("Error al subir el archivo. Revisa permisos.");
    }

    // Borrar PDF anterior si existe
    if ($ruta_existente && file_exists($directorio . basename($ruta_existente))) {
        unlink($directorio . basename($ruta_existente));
    }

    $pdf_path = "resultados/" . $nombreArchivo;
} else {
    // Mantener el PDF existente si no se sube uno nuevo
    $pdf_path = $ruta_existente;
}

/* =====================================================
   INSERTAR O ACTUALIZAR RESULTADO
   ===================================================== */
if ($id_resultado) {
    // UPDATE
    $stmt_upd = $conexion->prepare("
        UPDATE resultados 
        SET valor_numerico=?, valor_cualitativo=?, unidad=?, observaciones=?, interpretacion=?, capturado_por=?, url_archivo=?, fecha_captura=NOW()
        WHERE id_resultado=?
    ");
    $stmt_upd->bind_param("dssssisi", $valor_numerico, $valor_cualitativo, $unidad, $observaciones, $interpretacion, $id_usuario, $pdf_path, $id_resultado);
    $stmt_upd->execute();
    $stmt_upd->close();
} else {
    // INSERT
    $stmt_ins = $conexion->prepare("
        INSERT INTO resultados (id_orden_estudio, valor_numerico, valor_cualitativo, unidad, observaciones, interpretacion, capturado_por, url_archivo, fecha_captura)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt_ins->bind_param("idssssis", $id_orden_estudio, $valor_numerico, $valor_cualitativo, $unidad, $observaciones, $interpretacion, $id_usuario, $pdf_path);
    $stmt_ins->execute();
    $stmt_ins->close();
}

/* =====================================================
   ACTUALIZAR ESTADO DEL ESTUDIO
   ===================================================== */
$conexion->query("
    UPDATE orden_estudios
    SET estado = 'capturado'
    WHERE id_orden_estudio = $id_orden_estudio
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resultado guardado</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<script>
Swal.fire({
    icon: 'success',
    title: 'Resultado guardado',
    text: 'El estudio fue capturado correctamente. PDF actualizado si se subió uno nuevo.',
    confirmButtonColor: '#198754'
}).then(() => {
    history.back(); // regresar a la página anterior
});
</script>

</body>
</html>
