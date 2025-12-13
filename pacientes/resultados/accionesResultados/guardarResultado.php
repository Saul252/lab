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
   $nombreDocumento=0;
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

if (!empty($_FILES['pdf']['name'])) {

    // Validar extensión
    $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        die("Solo se permiten archivos PDF");
    }

    // RUTA CORRECTA: usa ruta absoluta desde el script actual
    $directorio = __DIR__ . "/../../resultados-laboratorio/"; // Ajusta según tu estructura real

    // Crear carpeta si no existe
    if (!is_dir($directorio)) {
        if (!mkdir($directorio, 0755, true)) {
            die("No se pudo crear la carpeta resultados. Verifica permisos.");
        }
    }

    // Generar nombre único
    $nombreArchivo = generarNombrePDF($id_orden_estudio);
    
    $rutaFinal = $directorio . $nombreArchivo;
   

    // Mover archivo a carpeta destino
    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $rutaFinal)) {
        // Guardamos la ruta relativa correcta para la BD o mostrar en el sistema
        $pdf_path = "resultados/" . $nombreArchivo;
        $nombreDocumento=$pdf_path;
    } else {
        die("Error al subir el archivo. Revisa permisos de la carpeta resultados.");
    }
}

/* =====================================================
   INSERTAR RESULTADO
   ===================================================== */
$stmt = $conexion->prepare("
    INSERT INTO resultados (
        id_orden_estudio,
        valor_numerico,
        valor_cualitativo,
        unidad,
        observaciones,
        interpretacion,
        capturado_por,
        url_archivo,
        fecha_captura
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param(
    "idssssis",
    $id_orden_estudio,
    $valor_numerico,
    $valor_cualitativo,
    $unidad,
    $observaciones,
    $interpretacion,
    $id_usuario,
    $nombreDocumento
);

$stmt->execute();

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
    text: 'El estudio fue capturado correctamente.',
    confirmButtonColor: '#198754'
}).then(() => {
    history.back(); // regresar a la página anterior
});
</script>

</body>
</html>
