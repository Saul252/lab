<?php
require "../../conexion.php";

$id_estudio = $_POST['id_estudio'];
$codigo = $_POST['codigo'];
$nombre = $_POST['nombre'];
$tipo_estudio = $_POST['tipo'];
$tipo_resultado = $_POST['tipo_resultado'];
$precio = $_POST['precio'];
$unidad = $_POST['unidad'];

$h_min = $_POST['rango_hombre_min'] !== "" ? $_POST['rango_hombre_min'] : null;
$h_max = $_POST['rango_hombre_max'] !== "" ? $_POST['rango_hombre_max'] : null;
$m_min = $_POST['rango_mujer_min'] !== "" ? $_POST['rango_mujer_min'] : null;
$m_max = $_POST['rango_mujer_max'] !== "" ? $_POST['rango_mujer_max'] : null;

$descripcion = $_POST['descripcion'];

$success = false;
$errorMessage = "";

$stmt = $conexion->prepare("
    UPDATE estudios SET
        codigo = ?, nombre = ?, tipo = ?, precio = ?, unidad = ?, tipo_resultado = ?,
        rango_hombre_min = ?, rango_hombre_max = ?,
        rango_mujer_min = ?, rango_mujer_max = ?,
        descripcion = ?
    WHERE id_estudio = ?
");

if ($stmt) {
    $stmt->bind_param(
        "sssdssddddsi",
        $codigo, $nombre, $tipo_estudio, $precio, $unidad, $tipo_resultado,
        $h_min, $h_max, $m_min, $m_max, $descripcion,
        $id_estudio
    );

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
    title: 'Estudio actualizado',
    text: 'Los cambios se guardaron correctamente.',
    confirmButtonColor: '#3085d6'
}).then(() => {
    window.location.href = "../catalogoEstudios.php?ok=1";
});
<?php else: ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: <?= json_encode("Ocurrió un error al actualizar: $errorMessage") ?>,
    confirmButtonColor: '#d33'
}).then(() => {
    window.history.back();
});
<?php endif; ?>
</script>
</body>
</html>
