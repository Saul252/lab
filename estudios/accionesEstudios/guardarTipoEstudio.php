<?php
require "../../conexion.php";

// Recibir datos
$codigo = $_POST["codigo"];
$nombre = $_POST["nombre"];
$precio = $_POST["precio"];
$unidad = $_POST["unidad"];
$tipo   = $_POST["tipo_resultado"];

$h_min = $_POST["rango_hombre_min"] !== "" ? $_POST["rango_hombre_min"] : "NULL";
$h_max = $_POST["rango_hombre_max"] !== "" ? $_POST["rango_hombre_max"] : "NULL";
$m_min = $_POST["rango_mujer_min"] !== "" ? $_POST["rango_mujer_min"] : "NULL";
$m_max = $_POST["rango_mujer_max"] !== "" ? $_POST["rango_mujer_max"] : "NULL";

$descripcion = $_POST["descripcion"];

$sql = "INSERT INTO estudios 
        (codigo, nombre, precio, unidad, tipo_resultado,
        rango_hombre_min, rango_hombre_max, 
        rango_mujer_min, rango_mujer_max, descripcion)
        VALUES
        ('$codigo', '$nombre', '$precio', '$unidad', '$tipo',
        $h_min, $h_max, $m_min, $m_max, '$descripcion')";

if ($conexion->query($sql)) {
    $success = true;
} else {
    $success = false;
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
    title: 'Estudio agregado',
    text: 'El estudio se registró correctamente.',
    confirmButtonColor: '#3085d6'
}).then(() => {
    window.location.href = "../estudios.php?ok=1";
});
<?php else: ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'Ocurrió un error al guardar: <?= $errorMessage ?>',
    confirmButtonColor: '#d33'
}).then(() => {
    window.history.back();
});
<?php endif; ?>
</script>
</body>
</html>
