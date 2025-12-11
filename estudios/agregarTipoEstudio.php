<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

require '../conexion.php'; 

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $unidad = $_POST['unidad'];
    $tipo_resultado = $_POST['tipo_resultado'];

    $rango_hombre_min = $_POST['rango_hombre_min'] ?: null;
    $rango_hombre_max = $_POST['rango_hombre_max'] ?: null;
    $rango_mujer_min  = $_POST['rango_mujer_min'] ?: null;
    $rango_mujer_max  = $_POST['rango_mujer_max'] ?: null;

    $descripcion = $_POST['descripcion'];

    $stmt = $conn->prepare("
        INSERT INTO estudios (
            codigo, nombre, precio, unidad, tipo_resultado,
            rango_hombre_min, rango_hombre_max,
            rango_mujer_min, rango_mujer_max,
            descripcion
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssdssdddds",
        $codigo,
        $nombre,
        $precio,
        $unidad,
        $tipo_resultado,
        $rango_hombre_min,
        $rango_hombre_max,
        $rango_mujer_min,
        $rango_mujer_max,
        $descripcion
    );

    if ($stmt->execute()) {
        $mensaje = "✔ Estudio agregado correctamente.";
    } else {
        $mensaje = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Nuevo Estudio</title>
</head>

<body class="bg-light">

<div class="container mt-4">

    <?php if ($mensaje): ?>
        <div class="alert alert-info"><?= $mensaje ?></div>
    <?php endif; ?>

    <div class="card shadow-lg border-0">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Agregar Nuevo Estudio</h4>
        </div>

        <div class="card-body">

            <form action="/lab/estudios/accionesEstudios/guardarTipoEstudio.php"method="POST">

                <!-- Datos principales -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Código *</label>
                     <input type="text" name="codigo" class="form-control" readonly required>

                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Nombre del Estudio *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Precio *</label>
                        <input type="number" step="0.01" min="0" name="precio" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unidad</label>
                        <input type="text" name="unidad" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo de Resultado *</label>
                        <select name="tipo_resultado" class="form-select" required>
                            <option value="numerico">Numérico</option>
                            <option value="cualitativo">Cualitativo</option>
                            <option value="ambos">Ambos</option>
                        </select>
                    </div>
                </div>

                <!-- Rangos -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        Rangos de Referencia
                    </div>
                    <div class="card-body">

                        <div class="row mb-3">
                            <h6>Hombres</h6>
                            <div class="col-md-3">
                                <label class="form-label">Mínimo</label>
                                <input type="number" step="0.01" name="rango_hombre_min" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Máximo</label>
                                <input type="number" step="0.01" name="rango_hombre_max" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <h6>Mujeres</h6>
                            <div class="col-md-3">
                                <label class="form-label">Mínimo</label>
                                <input type="number" step="0.01" name="rango_mujer_min" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Máximo</label>
                                <input type="number" step="0.01" name="rango_mujer_max" class="form-control">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"></textarea>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <button class="btn btn-success">Guardar Estudio</button>
                </div>

            </form>

        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const inputNombre = document.querySelector("input[name='nombre']");
    const inputCodigo = document.querySelector("input[name='codigo']");

    function generarCodigo(nombre) {
        // Limpia acentos
        nombre = nombre.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        // Toma primeras 3 letras del nombre como prefijo
        let prefijo = nombre.trim().substring(0, 3).toUpperCase();

        if (prefijo.length < 3) {
            prefijo = prefijo.padEnd(3, 'X'); // rellena si faltan letras
        }

        // Genera un número aleatorio de 3 dígitos
        let numero = Math.floor(Math.random() * 900) + 100;

        return `${prefijo}-${numero}`;
    }

    inputNombre.addEventListener("keyup", function() {
        if (inputNombre.value.trim() === "") {
            inputCodigo.value = "";
            return;
        }

        inputCodigo.value = generarCodigo(inputNombre.value);
    });

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    const selectTipo = document.querySelector("select[name='tipo_resultado']");
    const sectionRangos = document.querySelector("#contenedor-rangos");

    const inputsRangos = document.querySelectorAll(
        "input[name='rango_hombre_min'], input[name='rango_hombre_max'], input[name='rango_mujer_min'], input[name='rango_mujer_max']"
    );

    function actualizarVisibilidadRangos() {
        const valor = selectTipo.value;

        if (valor === "cualitativo") {
            sectionRangos.style.display = "none";
            inputsRangos.forEach(i => i.value = ""); // limpia valores
        } else {
            sectionRangos.style.display = "block";
        }
    }

    actualizarVisibilidadRangos();

    selectTipo.addEventListener("change", actualizarVisibilidadRangos);

});
</script>

</body>
</html>
