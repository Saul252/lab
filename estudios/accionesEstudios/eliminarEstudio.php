<?php
require "../../conexion.php";

// Verificar que venga ID
if (!isset($_GET['id'])) {
    die("ID de estudio no especificado.");
}

$id_estudio = $_GET['id'];
$success = false;
$errorMessage = "";

// Preparar delete seguro
$stmt = $conexion->prepare("DELETE FROM estudios WHERE id_estudio = ?");
if ($stmt) {
    $stmt->bind_param("i", $id_estudio);

    if ($stmt->execute()) {
        $success = true;
    } else {
        $errorMessage = $stmt->error;
    }

    $stmt->close();
} else {
    $errorMessage = $conexion->error;
}
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
<?php if ($success): ?>
Swal.fire({
    icon: 'success',
    title: 'Estudio eliminado',
    text: 'El estudio se eliminó correctamente.',
    confirmButtonColor: '#3085d6'
}).then(() => {
    window.location.href = "../catalogoEstudios.php?ok=2";
});
<?php else: ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: <?= json_encode("Ocurrió un error al eliminar: $errorMessage") ?>,
    confirmButtonColor: '#d33'
}).then(() => {
    window.history.back();
});
<?php endif; ?>
</script>
</body>
</html>
