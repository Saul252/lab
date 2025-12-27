<?php
session_start();
require "../../../conexion.php";

if (!isset($_SESSION['usuario'])) {
    die("Sesión no válida");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../../../vendor/autoload.php";

function enviarCorreoEstudios(string $email, string $nombre, string $folio): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'saulenriquealbatapia252@gmail.com';
        $mail->Password   = 'gvbf lhlo cmmr ghpn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('TU_CORREO@gmail.com', 'Laboratorio');
        $mail->addAddress($email, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'Sus estudios están listos';
        $mail->Body = "
            <h3>Hola {$nombre}</h3>
            <p>Le informamos que sus estudios con folio:</p>
            <p><b>{$folio}</b></p>
            <p>ya se encuentran disponibles.</p>
            <br>
            <p>Gracias por confiar en nosotros.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error correo: ' . $mail->ErrorInfo);
        return false;
    }
}


/* =====================================================
   FUNCIÓN PARA NOMBRE ÚNICO DE PDF
   ===================================================== */
function generarNombrePDF($id_orden_estudio)
{
    return "RES-" .
        $id_orden_estudio . "-" .
        date("YmdHis") . "-" .
        bin2hex(random_bytes(3)) .
        ".pdf";
}

/* =====================================================
   VALIDAR ID
   ===================================================== */
$nombreDocumento = 0;
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
    $directorio = __DIR__ . "/../../../pacientes/resultados-laboratorio/"; // Ajusta según tu estructura real

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
        $nombreDocumento = $rutaFinal;
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
    $nombreArchivo
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
/* =====================================================
   VALIDAR SI YA SE COMPLETARON TODOS LOS ESTUDIOS
   ===================================================== */

// Obtener la orden
$resOrden = $conexion->query("
    SELECT id_orden
    FROM orden_estudios
    WHERE id_orden_estudio = $id_orden_estudio
");
$id_orden = $resOrden->fetch_assoc()['id_orden'];

// Contar estudios pendientes
$resPendientes = $conexion->query("
    SELECT COUNT(*) total
    FROM orden_estudios
    WHERE id_orden = $id_orden
      AND estado != 'capturado'
");
$pendientes = (int)$resPendientes->fetch_assoc()['total'];

// Verificar si el correo ya fue enviado
$resCorreo = $conexion->query("
    SELECT correo_enviado
    FROM ordenes
    WHERE id_orden = $id_orden
");
$correo_enviado = (int)$resCorreo->fetch_assoc()['correo_enviado'];

// Si ya no hay pendientes y no se ha enviado correo
if ($pendientes === 0 && $correo_enviado === 0) {

    // Datos del paciente
    $resPaciente = $conexion->query("
        SELECT p.email, p.nombre, o.folio
        FROM ordenes o
        JOIN pacientes p ON p.id_paciente = o.id_paciente
        WHERE o.id_orden = $id_orden
    ");
    $paciente = $resPaciente->fetch_assoc();

    if (!empty($paciente['email'])) {

        if (enviarCorreoEstudios(
            $paciente['email'],
            $paciente['nombre'],
            $paciente['folio']
        )) {
            // Marcar correo como enviado
            $conexion->query("
                UPDATE ordenes
                SET correo_enviado = 1
                WHERE id_orden = $id_orden
            ");
        }
    }
}

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