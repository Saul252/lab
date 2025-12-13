<?php
require "../../../conexion.php";
session_start();

/* ==========================================================
   VALIDAR SESIÓN
   ========================================================== */
if (!isset($_SESSION["usuario"])) {
    header("Location: /lab/index.php");
    exit();
}

/* ==========================================================
   VALIDAR PETICIÓN
   ========================================================== */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Método inválido");
}

/* ==========================================================
   RECIBIR DATOS
   ========================================================== */
$id_paciente = intval($_POST["id_paciente"]);
$fecha_cita  = $_POST["fecha_cita"] ?? null;
$hora_cita   = $_POST["hora_cita"] ?? null;
$crear_cita  = isset($_POST["crear_cita"]);
$estudios    = $_POST["estudios"] ?? [];

if (!$id_paciente || empty($estudios)) {
    die("Faltan datos importantes");
}

/* ==========================================================
   INICIAR TRANSACCIÓN
   ========================================================== */
$conexion->begin_transaction();

try {

    /* ==========================================================
       1. GENERAR FOLIO ÚNICO
       ========================================================== */
    $fechaFolio = date("YmdHis");
    $folio = "ORD-" . $fechaFolio . "-" . rand(100, 999);

    /* ==========================================================
       2. CREAR CITA SI SE REQUIERE
       ========================================================== */
    $id_cita = null;

    if ($crear_cita && $fecha_cita && $hora_cita) {

        // Verificar que no exista otra cita en el mismo horario
        $stmt = $conexion->prepare("
            SELECT id_cita FROM citas 
            WHERE fecha_cita = ? AND hora_cita = ?
        ");
        $stmt->bind_param("ss", $fecha_cita, $hora_cita);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            throw new Exception("Ya existe una cita programada en ese horario.");
        }

        // Insertar cita
        $stmt = $conexion->prepare("
            INSERT INTO citas (id_paciente, fecha_cita, hora_cita, estado)
            VALUES (?, ?, ?, 'programada')
        ");
        $stmt->bind_param("iss", $id_paciente, $fecha_cita, $hora_cita);
        $stmt->execute();
        $id_cita = $stmt->insert_id;
        $stmt->close();
    }

    /* ==========================================================
       3. CALCULAR TOTAL DE LOS ESTUDIOS
       ========================================================== */
    $ids = implode(",", array_map('intval', $estudios));
    $result = $conexion->query("SELECT id_estudio, precio FROM estudios WHERE id_estudio IN ($ids)");

    $total = 0;
    $precios = [];

    while ($row = $result->fetch_assoc()) {
        $precios[$row["id_estudio"]] = $row["precio"];
        $total += $row["precio"];
    }

    /* ==========================================================
       4. CREAR ORDEN
       ========================================================== */
    $stmt = $conexion->prepare("
        INSERT INTO ordenes (
            folio, id_paciente, id_cita, estado, total
        ) VALUES (?, ?, ?, 'pendiente', ?)
    ");
    $stmt->bind_param("siid", $folio, $id_paciente, $id_cita, $total);
    $stmt->execute();
    $id_orden = $stmt->insert_id;
    $stmt->close();

    /* ==========================================================
       5. INSERTAR ESTUDIOS EN ORDEN_ESTUDIOS
       ========================================================== */
    $stmt = $conexion->prepare("
        INSERT INTO orden_estudios (id_orden, id_estudio, estado)
        VALUES (?, ?, 'pendiente')
    ");

    foreach ($estudios as $id_estudio) {
        $stmt->bind_param("ii", $id_orden, $id_estudio);
        $stmt->execute();
    }

    $stmt->close();

    /* ==========================================================
       6. SI TODO OK → CONFIRMAR TRANSACCIÓN
       ========================================================== */
    $conexion->commit();

} catch (Exception $e) {

    $conexion->rollback();
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Guardando...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if (!isset($error)): ?>

<script>
Swal.fire({
    icon: "success",
    title: "Orden creada correctamente",
    html: "El folio es:<br><b><?= $folio ?></b>",
    confirmButtonText: "OK"
}).then(() => {
    window.location.href = "/lab/pacientes/pacientes.php";
});
</script>

<?php else: ?>

<script>
Swal.fire({
    icon: "error",
    title: "Error al crear la orden",
    text: "<?= $error ?>",
});
</script>

<?php endif; ?>

</body>
</html>
