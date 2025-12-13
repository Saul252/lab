<?php
require "../../conexion.php";

$id = $_POST["id"];
$nombre = $_POST["nombre"];
$edad = $_POST["edad"];
$sexo = $_POST["sexo"];
$domicilio = $_POST["domicilio"];
$telefono = $_POST["telefono"];
$email = $_POST["email"];
$medico = $_POST["medico_solicitante"];

$sql = "UPDATE pacientes SET 
        nombre='$nombre',
        edad='$edad',
        sexo='$sexo',
        domicilio='$domicilio',
        telefono='$telefono',
        email='$email',
        medico_solicitante='$medico'
        WHERE id_paciente=$id";

if ($conexion->query($sql)) {

    
    header("Location: /lab/pacientes/pacientes.php?actualizado=1");
} else {
    echo "Error: " . $conexion->error;
}
?>
