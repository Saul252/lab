<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Reactivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
       <link rel="stylesheet" href="/lab/css/almacen/agregarReactivo.css">
    
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card card-custom">
                <h3 class="mb-4 text-center"><i class="bi bi-box-seam me-2"></i>Agregar Reactivo y Lote Inicial</h3>
                
                <form id="form-reactivo">
                    
                    <!-- Datos del Reactivo -->
                    <div class="section-title">Datos del Reactivo</div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-tag me-1"></i>Nombre *</label>
                        <input type="text" name="nombre" class="form-control form-control-lg" placeholder="Nombre del reactivo" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-card-text me-1"></i>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción del reactivo"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-box-arrow-in-down-right me-1"></i>Unidad</label>
                        <input type="text" name="unidad" class="form-control" placeholder="Ej: ml, mg, g">
                    </div>

                    <!-- Lote Inicial -->
                    <div class="section-title mt-4">Lote Inicial</div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-upc-scan me-1"></i>Número de Lote *</label>
                        <input type="text" name="numero_lote" class="form-control" placeholder="Ej: L12345" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-calendar-event me-1"></i>Fecha de Caducidad</label>
                        <input type="date" name="fecha_caducidad" class="form-control">
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-stack me-1"></i>Cantidad *</label>
                        <input type="number" name="cantidad" min="1" class="form-control" placeholder="Cantidad inicial" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-2"></i>Guardar Reactivo + Lote</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ======================= GUARDAR REACTIVO + LOTE =======================
document.getElementById('form-reactivo').addEventListener('submit', function(e){
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/lab/almacen/accionesAlmacen/guardarReactivo.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            Swal.fire({
                icon: 'success',
                title: 'Reactivo guardado',
                text: 'El reactivo y su lote inicial se registraron correctamente.',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                   window.history.back();

            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error inesperado',
            confirmButtonColor: '#d33'
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
