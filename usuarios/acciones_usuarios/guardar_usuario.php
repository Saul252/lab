<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo admin puede registrar
if ($_SESSION["rol"] !== "admin") {
    return;
}

require "../../conexion.php";

$nombre = $_POST["nombreCompleto"];
$usuario = $_POST["usuario"];
$password = $_POST["password"];
$password2 = $_POST["password2"];
$rol = $_POST["rol"];

if ($password !== $password2) {
    die("Las contraseñas no coinciden. <a href='registro.php'>Volver</a>");
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, usuario, password, rol, activo)
        VALUES ('$nombre', '$usuario', '$password_hash', '$rol', 1)";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guardando usuario...</title>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
if ($conexion->query($sql)) {
    ?>

    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Usuario creado con éxito!',
            text: 'El usuario fue registrado correctamente.',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = '../administarUsuarios.php';
        });
    </script>

    <?php
} else {
    ?>

    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error al registrar',
            text: '<?= $conexion->error ?>',
            confirmButtonText: 'Cerrar'
        }).then(() => {
            window.location.href = '../registro.php';
        });
    </script>

    <?php
}
?>

</body>
</html>
