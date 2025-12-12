<?php
require "../../../conexion.php";

$id_orden = intval($_POST["id_orden"]);

// ---------------------------------------------
// 1. ACTUALIZAR CITA
// ---------------------------------------------
$fecha_cita = $_POST["fecha_cita"] ?? null;
$hora_cita  = $_POST["hora_cita"] ?? null;
$id_cita    = intval($_POST["id_cita"] ?? 0); // 🔹 Aquí ahora sí llega el ID de la cita

if ($id_cita > 0 && $fecha_cita && $hora_cita) {
    $conexion->query("
        UPDATE citas 
        SET fecha_cita = '$fecha_cita',
            hora_cita  = '$hora_cita'
        WHERE id_cita = $id_cita
    ");
}



// ---------------------------------------------
// 2. QUITAR ESTUDIOS (ELIMINAR, NO CANCELAR)
// ---------------------------------------------
$quitar = $_POST["quitar_estudio"] ?? [];

if (!empty($quitar)) {
    $ids = implode(",", array_map("intval", $quitar));
    
    // 🔥 Aquí eliminamos realmente
    $conexion->query("DELETE FROM orden_estudios WHERE id_orden_estudio IN ($ids)");
}



// ---------------------------------------------
// 3. AGREGAR ESTUDIOS NUEVOS
// ---------------------------------------------
$nuevos = isset($_POST["agregar_estudios"]) 
        ? json_decode($_POST["agregar_estudios"], true) 
        : [];

if (!empty($nuevos)) {
    foreach ($nuevos as $id_estudio) {
        $id_estudio = intval($id_estudio);

        // Evitar duplicados
        $conexion->query("
            INSERT INTO orden_estudios (id_orden, id_estudio, estado)
            VALUES ($id_orden, $id_estudio, 'pendiente')
        ");
    }
}



// ---------------------------------------------
// 4. RECALCULAR TOTAL
// ---------------------------------------------
$total = 0;

$sqlTotal = "
SELECT e.precio
FROM orden_estudios oe
JOIN estudios e ON e.id_estudio = oe.id_estudio
WHERE oe.id_orden = $id_orden
";

$resTotal = $conexion->query($sqlTotal);

while ($row = $resTotal->fetch_assoc()) {
    $total += floatval($row["precio"]);
}

$conexion->query("UPDATE ordenes SET total = $total WHERE id_orden = $id_orden");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
    icon: "success",
    title: "Cambios guardados",
    text: "La orden se actualizó correctamente.",
    timer: 1800,
    showConfirmButton: false
}).then(() => {
    window.location.href = "/lab/pacientes/ordenesEstudios/ediarOrden.php?id=<?= $id_orden ?>";
});
</script>

</body>
</html>
