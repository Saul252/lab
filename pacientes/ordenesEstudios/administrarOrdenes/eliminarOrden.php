<?php
session_start();
require "../../../conexion.php";

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /lab/login.php");
    exit;
}

$id_orden = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_orden <= 0) {
    header("Location: /lab/estudios/estudios.php");
    exit;
}

if (isset($_GET['confirm']) && $_GET['confirm'] === '1') {

    $conexion->begin_transaction();

    try {

        // 1️⃣ Obtener id_cita asociada
        $stmt = $conexion->prepare("SELECT id_cita FROM ordenes WHERE id_orden = ?");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $stmt->bind_result($id_cita);
        $stmt->fetch();
        $stmt->close();

        // 2️⃣ Eliminar resultados
        $stmt = $conexion->prepare("
            DELETE r FROM resultados r
            INNER JOIN orden_estudios oe 
                ON r.id_orden_estudio = oe.id_orden_estudio
            WHERE oe.id_orden = ?
        ");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $stmt->close();

        // 3️⃣ Eliminar estudios de la orden
        $stmt = $conexion->prepare("DELETE FROM orden_estudios WHERE id_orden = ?");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $stmt->close();

        // 4️⃣ Eliminar pagos (si existen)
        $stmt = $conexion->prepare("DELETE FROM pagos WHERE id_orden = ?");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $stmt->close();

        // 5️⃣ Eliminar tickets
        $stmt = $conexion->prepare("DELETE FROM tickets WHERE id_orden = ?");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $stmt->close();

        // 6️⃣ Eliminar orden
        $stmt = $conexion->prepare("DELETE FROM ordenes WHERE id_orden = ?");
        $stmt->bind_param("i", $id_orden);
        $stmt->execute();
        $stmt->close();

        // 7️⃣ Eliminar cita (si existía)
        if (!empty($id_cita)) {
            $stmt = $conexion->prepare("DELETE FROM citas WHERE id_cita = ?");
            $stmt->bind_param("i", $id_cita);
            $stmt->execute();
            $stmt->close();
        }

        $conexion->commit();
        $success = true;

    } catch (Exception $e) {
        $conexion->rollback();
        $success = false;
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Orden</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
<?php if (!isset($_GET['confirm'])): ?>

Swal.fire({
    title: '¿Eliminar orden?',
    text: 'Esta acción eliminará la orden y todos los estudios asociados.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
}).then((result) => {
    if (result.isConfirmed) {
        window.location.href = 'eliminarOrden.php?id=<?= $id_orden ?>&confirm=1';
    } else {
        window.history.back();
    }
});

<?php elseif ($success): ?>

Swal.fire({
    icon: 'success',
    title: 'Orden eliminada',
    text: 'La orden fue eliminada correctamente.',
    timer: 1800,
    showConfirmButton: false
}).then(() => {
    window.location.href = '/lab/estudios/estudios.php';
});

<?php else: ?>

Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'No se pudo eliminar la orden.'
}).then(() => {
    window.location.href = '/lab/estudios/estudios.php';
});

<?php endif; ?>
</script>

</body>
</html>
