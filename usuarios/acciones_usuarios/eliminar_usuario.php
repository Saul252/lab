<?php
session_start();
require "../../conexion.php";

/* ===========================================
   VALIDAR SESIÓN Y ROL
   =========================================== */
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "admin") {
    die("Acceso denegado. Solo el administrador puede eliminar usuarios.");
}

/* ===========================================
   VALIDAR ID RECIBIDO
   =========================================== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: administarUsuarios.php");
    exit;
}

$id = (int) $_GET['id'];

/* ===========================================
   EJECUTAR ELIMINACIÓN
   =========================================== */
$query = "DELETE FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($query);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("i", $id);
$exito = $stmt->execute() && $stmt->affected_rows > 0;

$stmt->close();
$conexion->close();

/* ===========================================
   SWEET ALERT (ÉXITO O ERROR)
   =========================================== */

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Eliminar usuario</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if ($exito): ?>

<script>
Swal.fire({
    icon: "success",
    title: "Usuario eliminado",
    text: "El usuario fue eliminado exitosamente",
    confirmButtonText: "Aceptar"
}).then(() => {
    window.location.href = "../administarUsuarios.php";
});
</script>

<?php else: ?>

<script>
Swal.fire({
    icon: "error",
    title: "Error",
    text: "No se pudo eliminar el usuario",
    confirmButtonText: "Aceptar"
}).then(() => {
    window.location.href = "../administarUsuarios.php";
});
</script>

<?php endif; ?>

</body>
</html>
