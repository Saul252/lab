<?php
session_start();

?>
<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<title>Agregar Paciente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">
    <h3 class="mb-3">Registrar Paciente</h3>

    <div class="card shadow">
        <div class="card-body">

            <form action="accionesPacientes.php/guardarPaciente.php" method="POST">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label>Edad</label>
                        <input type="number" name="edad" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Sexo</label>
                        <select name="sexo" class="form-control">
                            <option value="H">Hombre</option>
                            <option value="M">Mujer</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Domicilio</label>
                    <input type="text" name="domicilio" class="form-control">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Médico Solicitante</label>
                    <input type="text" name="medico_solicitante" class="form-control">
                </div>

                <button class="btn btn-success">Guardar Paciente</button>
                <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>

            </form>
        </div>
    </div>
</div>

</body>
</html>
