<?php
require "../../conexion.php";

$nombre = $_POST["nombre"];
$edad = $_POST["edad"];
$sexo = $_POST["sexo"];
$domicilio = $_POST["domicilio"];
$telefono = $_POST["telefono"];
$email = $_POST["email"];
$medico = $_POST["medico_solicitante"];

$sql = "INSERT INTO pacientes (nombre, edad, sexo, domicilio, telefono, email, medico_solicitante)
        VALUES ('$nombre', '$edad', '$sexo', '$domicilio', '$telefono', '$email', '$medico')";

if ($conexion->query($sql)) {
    
    header("Location:../pacientes.php?ok=1");
} else {
    echo "Error: " . $conexion->error;
}
?>
