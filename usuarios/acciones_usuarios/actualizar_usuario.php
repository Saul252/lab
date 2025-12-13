<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo administradores pueden editar
if ($_SESSION["rol"] !== "admin") {
    die("No tienes permisos para editar usuarios.");
}

require "../../conexion.php";


// Verificar que llegan los datos
if (!isset($_POST["id_usuario"])) {
    die("No llegó el ID del usuario");
}

// Recibir datos
$id_usuario   = $_POST["id_usuario"];
$nombre       = $_POST["nombreCompleto"];
$usuario      = $_POST["usuario"];
$rol          = $_POST["rol"];
$password     = $_POST["password"];
$password2    = $_POST["password2"];

// Verificar contraseñas
if (!empty($password) || !empty($password2)) {

    if ($password !== $password2) {
        echo "<script>
            alert('Las contraseñas no coinciden');
            window.location='editar_usuario.php?id=$id_usuario';
        </script>";
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios
            SET nombre = '$nombre',
                usuario = '$usuario',
                password = '$password_hash',
                rol = '$rol'
            WHERE id_usuario = $id_usuario";
} else {

    $sql = "UPDATE usuarios
            SET nombre = '$nombre',
                usuario = '$usuario',
                rol = '$rol'
            WHERE id_usuario = $id_usuario";
}

// Ejecutar UPDATE
if ($conexion->query($sql)) {
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
    title: 'Usuario actualizado',
    text: 'Los datos del usuario fueron modificados correctamente',
    icon: 'success',
    confirmButtonText: 'Volver a la lista'
}).then(() => {
    window.location = '../administarUsuarios.php';
});
</script>

</body>
</html>

<?php
} else {
    echo "Error SQL: " . $conexion->error;
}
