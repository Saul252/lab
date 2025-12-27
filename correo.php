<?php
require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$estado = null;
$mensaje = null;

function enviarCorreoPrueba(string $email)
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
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = '📧 Prueba de correo';
        $mail->Body    = '<h3>Correo enviado correctamente</h3>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $estado = 'error';
        $mensaje = 'Correo inválido';
    } else {
        $resultado = enviarCorreoPrueba($correo);

        if ($resultado === true) {
            $estado = 'success';
            $mensaje = 'El correo se envió correctamente';
        } else {
            $estado = 'error';
            $mensaje = $resultado;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Prueba correo</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <form method="POST">
        <input type="email" name="correo" placeholder="correo@ejemplo.com" required>
        <button type="submit">Enviar correo</button>
    </form>

    <?php if ($estado): ?>
        <script>
            Swal.fire({
                icon: '<?= $estado ?>',
                title: '<?= $estado === "success" ? "Éxito" : "Error" ?>',
                text: <?= json_encode($mensaje) ?>
            });
        </script>
    <?php endif; ?>

</body>

</html>