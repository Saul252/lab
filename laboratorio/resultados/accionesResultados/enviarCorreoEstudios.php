<?php
require_once __DIR__ . "/../../../vendor/autoload.php";
require_once __DIR__ . "/../../../conexion.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function   enviarCorreoEstudios($id_orden_estudio, $conexion) {

    // 1️⃣ Obtener la orden
    $sql = "
        SELECT 
            oe.id_orden,
            o.correo_resultados_enviado,
            pa.email,
            pa.nombre
        FROM orden_estudios oe
        JOIN ordenes o ON o.id_orden = oe.id_orden
        JOIN pacientes pa ON pa.id_paciente = o.id_paciente
        WHERE oe.id_orden_estudio = ?
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_orden_estudio);
    $stmt->execute();
    $orden = $stmt->get_result()->fetch_assoc();

    if (!$orden) return;

    // ⛔ Ya se envió el correo
    if ($orden['correo_resultados_enviado'] == 1) return;

    $id_orden = $orden['id_orden'];

    // 2️⃣ Verificar si TODOS los estudios están capturados
    $sql = "
        SELECT COUNT(*) total,
               SUM(estado = 'capturado') completados
        FROM orden_estudios
        WHERE id_orden = ?
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_orden);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res['total'] != $res['completados']) return;

    // 3️⃣ ENVIAR CORREO
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'saulenriquealbatapia252@gmail.com'; 
        $mail->Password   = 'Sigueadelante1';      
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('TU_CORREO@gmail.com', 'Laboratorio');
        $mail->addAddress($orden['email'], $orden['nombre']);

        $mail->isHTML(true);
        $mail->Subject = '✅ Sus estudios ya están listos';
        $mail->Body = "
            <h3>Hola {$orden['nombre']}</h3>
            <p>Le informamos que <b>todos sus estudios de laboratorio ya están listos</b>.</p>
            <p>Puede consultarlos en el laboratorio.</p>
            <br>
            <small>Este es un mensaje automático.</small>
        ";

        $mail->send();

        // 4️⃣ Marcar como enviado
        $conexion->query("
            UPDATE ordenes 
            SET correo_resultados_enviado = 1
            WHERE id_orden = $id_orden
        ");

    } catch (Exception $e) {
        error_log("Error correo laboratorio: " . $mail->ErrorInfo);
    }
}
