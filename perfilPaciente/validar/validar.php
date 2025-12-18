<?php session_start();
require "../../conexion.php";

$email = trim($_POST['email']);
$folio = trim($_POST['folio']);

$sql = "
SELECT p.id_paciente
FROM pacientes p
JOIN ordenes o ON o.id_paciente = p.id_paciente
WHERE p.email = ?
AND o.folio = ?
LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $email, $folio);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: login.php?error=1");
    exit;
}

$data = $res->fetch_assoc();

/* SESIÓN TEMPORAL */
$_SESSION['paciente_publico'] = $data['id_paciente'];

header("Location: ../perfil.php");
exit;
?>
