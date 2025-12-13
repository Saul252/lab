<?php
require "../conexion.php";

if (!isset($_GET["id"])) {
    header("Location: pacientes.php");
    exit;
}

$id = $_GET["id"];
$sql = $conexion->query("SELECT * FROM pacientes WHERE id_paciente = $id");
$pac = $sql->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<title>Editar Paciente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">
    <h3>Editar Paciente</h3>

    <div class="card shadow">
        <div class="card-body">

            <form action="accionesPacientes.php/actualizarPaciente.php" method="POST">

                <input type="hidden" name="id" value="<?= $pac['id_paciente'] ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?= $pac['nombre'] ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label>Edad</label>
                        <input type="number" name="edad" class="form-control" value="<?= $pac['edad'] ?>">
                    </div>

                    <div class="col-md-3">
                        <label>Sexo</label>
                        <select name="sexo" class="form-control">
                            <option <?= $pac['sexo']=="H"?"selected":"" ?>>H</option>
                            <option <?= $pac['sexo']=="M"?"selected":"" ?>>M</option>
                            <option <?= $pac['sexo']=="Otro"?"selected":"" ?>>Otro</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Domicilio</label>
                    <input type="text" name="domicilio" class="form-control" value="<?= $pac['domicilio'] ?>">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= $pac['telefono'] ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= $pac['email'] ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Médico solicitante</label>
                    <input type="text" name="medico_solicitante" class="form-control" value="<?= $pac['medico_solicitante'] ?>">
                </div>

                <button class="btn btn-primary">Actualizar</button>
                <a href="/lab/pacientes/pacientes.php" class="btn btn-secondary">Cancelar</a>

            </form>

        </div>
    </div>

</div>

</body>
</html>
