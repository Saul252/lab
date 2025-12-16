<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

require '../conexion.php';

if (!isset($_GET['id'])) {
    die("ID de estudio no especificado.");
}

$id_estudio = $_GET['id'];

// Traer datos del estudio
$stmt = $conexion->prepare("SELECT * FROM estudios WHERE id_estudio = ?");
$stmt->bind_param("i", $id_estudio);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Estudio no encontrado.");
}

$estudio = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Editar Estudio</title>
</head>
<body class="bg-light">
<div class="container mt-4">

<div class="card shadow-lg border-0">
    <div class="card-header bg-dark text-white">
        <h4 class="mb-0">Editar Estudio</h4>
    </div>

    <div class="card-body">
        <form action="accionesEstudios/actualizarEstudio.php" method="POST">
            <input type="hidden" name="id_estudio" value="<?= $estudio['id_estudio'] ?>">

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Código *</label>
                    <input type="text" name="codigo" class="form-control form-control-sm" required value="<?= htmlspecialchars($estudio['codigo']) ?>">
                </div>

                <div class="col-md-5">
                    <label class="form-label">Nombre del Estudio *</label>
                    <input type="text" name="nombre" class="form-control form-control-sm" required value="<?= htmlspecialchars($estudio['nombre']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tipo de estudio *</label>
                    <select class="form-select form-select-sm" name="tipo" required>
                        <?php
                        $tipos = ['sangre','orina','heces','microbiologia','inmunologia','hormonas','imagen','cardiologia','patologia','genetica','otro'];
                        foreach($tipos as $tipo) {
                            $sel = ($estudio['tipo'] === $tipo) ? "selected" : "";
                            echo "<option value='$tipo' $sel>".ucfirst($tipo)."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Precio *</label>
                    <input type="number" step="0.01" min="0" name="precio" class="form-control" required value="<?= $estudio['precio'] ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Unidad</label>
                    <input type="text" name="unidad" class="form-control" value="<?= htmlspecialchars($estudio['unidad']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tipo de Resultado *</label>
                    <select name="tipo_resultado" class="form-select" required>
                        <?php
                        $resultados = ['numerico','cualitativo','ambos'];
                        foreach($resultados as $res) {
                            $sel = ($estudio['tipo_resultado'] === $res) ? "selected" : "";
                            echo "<option value='$res' $sel>".ucfirst($res)."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">Rangos de Referencia</div>
                <div class="card-body" id="contenedor-rangos">
                    <div class="row mb-3">
                        <h6>Hombres</h6>
                        <div class="col-md-3">
                            <label class="form-label">Mínimo</label>
                            <input type="number" step="0.01" name="rango_hombre_min" class="form-control" value="<?= $estudio['rango_hombre_min'] ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Máximo</label>
                            <input type="number" step="0.01" name="rango_hombre_max" class="form-control" value="<?= $estudio['rango_hombre_max'] ?>">
                        </div>
                    </div>

                    <div class="row">
                        <h6>Mujeres</h6>
                        <div class="col-md-3">
                            <label class="form-label">Mínimo</label>
                            <input type="number" step="0.01" name="rango_mujer_min" class="form-control" value="<?= $estudio['rango_mujer_min'] ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Máximo</label>
                            <input type="number" step="0.01" name="rango_mujer_max" class="form-control" value="<?= $estudio['rango_mujer_max'] ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($estudio['descripcion']) ?></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <button onclick="history.back()" class="btn btn-secondary">← Regresar</button>
                <button class="btn btn-primary">Actualizar Estudio</button>
            </div>

        </form>
    </div>
</div>

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
            inputsRangos.forEach(i => i.value = "");
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
