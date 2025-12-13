<?php
require "../../conexion.php";

if (!isset($_GET["id"])) {
    header("Location: listar_pacientes.php");
    exit;
}

$id = $_GET["id"];

$sql = "DELETE FROM pacientes WHERE id_paciente = $id";

if ($conexion->query($sql)) {
    header("Location:../pacientes.php?ok=1");
} else {
    echo "Error: " . $conexion->error;
}
?>
