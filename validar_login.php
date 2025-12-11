<?php
session_start();
require "conexion.php";

$usuario = $_POST["usuario"];
$password = $_POST["password"];

$sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    $row = $resultado->fetch_assoc();

    if (password_verify($password, $row['password'])) {

        $_SESSION["usuario"] = $row["usuario"];
        $_SESSION["id"] = $row["id_usuario"];
        $_SESSION["rol"] = $row["rol"];
        

        header("Location: bienvenida.php");
        exit();

    } else {
        echo "<script>alert('Contraseña incorrecta');window.location='login.php'</script>";
    }
} else {
    echo "<script>alert('El usuario no existe');window.location='login.php'</script>";
}
?>
