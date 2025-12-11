<?php
// ordenesEstudios/crearOrden.php
require_once "../../conexion.php"; // usa require_once para que falle si no existe
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: /lab/index.php");
    exit();
}
// Validar id paciente
if (!isset($_GET['id'])) {
    die("No se recibió ID del paciente");
}

$id_paciente = intval($_GET['id']);

// Obtener datos del paciente
$stmt = $conexion->prepare("SELECT * FROM pacientes WHERE id_paciente = ?");
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die("Paciente no encontrado");
}
$paciente = $res->fetch_assoc();
$stmt->close();

// Obtener catálogo de estudios
$estudios_q = $conexion->query("SELECT id_estudio, codigo, nombre, precio, tipo_resultado FROM estudios ORDER BY nombre ASC");
$estudios = $estudios_q->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Crear Orden - <?= htmlspecialchars($paciente['nombre']) ?></title>

  <!-- Bootstrap 5 -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">
  <style>
    /* Estética ligera dentro del propio archivo */
    .card-rounded { border-radius: 12px; }
    .table-hover tbody tr:hover { background-color: #f7fbff; }
    .small-muted { font-size: .9rem; color: #6c757d; }
    /* que la tabla de estudios no rompa en pantallas pequeñas */
    .estudios-table { max-height: 360px; overflow: auto; display:block; }
    /* resumen fijo visual */
    .summary-card { position: sticky; top: 16px; }
    /* highlight checkbox row */
    .row-selected { background: #e9f5ff; }
  </style>
</head>
<body class="bg-light">
    
  <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <button class="btn btn-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">

                <i class="bi bi-list" style="font-size: 1.2rem;"></i> <svg xmlns="http://www.w3.org/2000/svg" width="28"
                    height="28" fill="white" class="bi bi-list" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M2.5 12.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11z" />
                </svg>
                Menu

            </button>

            <div class="offcanvas offcanvas-start bg-dark" data-bs-scroll="true" tabindex="-1"
                id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title text-white bg-dark" id="offcanvasWithBothOptionsLabel">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>

                </div>
                <div class="offcanvas-body">
                    <?php if ($_SESSION["rol"] == "admin") { ?>

                    <div class="d-flex flex-column p-3 text-white bg-dark" style="height: 90vh; width: 100%;">

                        <!-- Título o logo -->

                        <ul class="nav nav-pills flex-column mb-auto">

                            <li class="nav-item">
                                <a href="/lab/bienvenida.php" class="nav-link text-white  hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="me-2">
                                        <rect x="2" y="2" width="6" height="6" rx="1"></rect>
                                        <rect x="10" y="2" width="4" height="9" rx="1"></rect>
                                        <rect x="2" y="10" width="6" height="4" rx="1"></rect>
                                        <rect x="10" y="12" width="4" height="3" rx="1"></rect>
                                    </svg>

                                    Dashboard
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/usuarios/administarUsuarios.php" class="nav-link text-white  hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white"
                                        class="bi bi-people-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0-2-5.235A3 3 0 0 0 11 8z" />
                                        <path
                                            d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z" />
                                        <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
                                    </svg>

                                    Usuarios
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/pacientes/pacientes.php" class="nav-link text-white active hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                        viewBox="0 0 16 16">
                                        <path d="M3 1h5l2 2h3v12H3z" />
                                    </svg>

                                    Pacientes
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/estudios/estudios.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="me-2">
                                        <rect x="3" y="2" width="10" height="14" rx="1"></rect>
                                        <line x1="5" y1="6" x2="11" y2="6"></line>
                                        <line x1="5" y1="9" x2="11" y2="9"></line>
                                        <line x1="5" y1="12" x2="9" y2="12"></line>
                                    </svg>


                                    Estudios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/lab/citas/citas.php" class="nav-link text-white hoverbutton">
                                    <!-- Icono SVG: Citas (calendario + reloj) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        viewBox="0 0 24 24" aria-hidden="true" role="img">
                                        <!-- Calendario -->
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <path d="M16 2v4M8 2v4M3 10h18"></path>
                                        <!-- Reloj pequeño -->
                                        <circle cx="12" cy="16" r="3"></circle>
                                        <path d="M12 14v2l1 1"></path>
                                    </svg>

                                    Citas
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="/lab/almacen/almacen.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white"
                                        viewBox="0 0 16 16">
                                        <path d="M2 3l6-2 6 2v10l-6 2-6-2V3z" />
                                        <path d="M2 3l6 2 6-2" />
                                    </svg>

                                    Almacén
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/lab/caja/caja.php" class="nav-link text-white hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="me-2">
                                        <path d="M2 4l6-2 6 2v10l-6 2-6-2z"></path>
                                        <path d="M2 4l6 3 6-3"></path>
                                    </svg>

                                    Caja
                                </a>
                            </li>

                        </ul>

                        <hr>

                        <!-- Perfil -->
                        <div class="dropup">
                            <a href="#"
                                class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                                id="userMenu" data-bs-toggle="dropdown">

                                <img src="https://github.com/mdo.png" alt="" width="32" height="32"
                                    class="rounded-circle me-2">

                                <strong><?php echo $_SESSION['usuario']; ?></strong>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                                <li><a class="dropdown-item" href="#">Perfil</a></li>
                                <li><a class="dropdown-item" href="#">Ajustes</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/lab/logout.php">Cerrar sesión</a></li>
                            </ul>
                        </div>

                    </div>

                    <?php } ?>

                </div>
            </div>
            <span class="navbar-brand">Pacientes</span>

            <div>
                <div style=" margin-right: 50px !important;" class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                        id="userMenu" data-bs-toggle="dropdown">

                        <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">

                        <strong><?php echo $_SESSION['usuario']; ?></strong>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">

                        <li><a class="dropdown-item" href="/lab/logout.php">Cerrar sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
<div class="container py-4">

  <div class="row g-3">
    <!-- Datos paciente -->
    <div class="col-lg-4">
      <div class="card card-rounded shadow-sm mb-3 summary-card">
        <div class="card-body">
          <h5 class="card-title mb-2">Paciente</h5>
          <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($paciente['nombre']); ?></p>
          <p class="mb-1"><strong>Edad:</strong> <?= htmlspecialchars($paciente['edad']); ?></p>
          <p class="mb-1"><strong>Sexo:</strong> <?= htmlspecialchars($paciente['sexo']); ?></p>
          <p class="mb-1"><strong>Teléfono:</strong> <?= htmlspecialchars($paciente['telefono']); ?></p>
          <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($paciente['email']); ?></p>
          <p class="mb-0 small-muted"><strong>Médico:</strong> <?= htmlspecialchars($paciente['medico_solicitante']); ?></p>
        </div>
      </div>

      <!-- Resumen de selección -->
      <div class="card card-rounded shadow-sm">
        <div class="card-body">
          <h6 class="mb-2">Resumen selección</h6>
          <div id="resumenLista" style="min-height:80px;">
            <p class="small-muted">No hay estudios seleccionados</p>
          </div>

          <hr>

          <p class="mb-1"><strong>Total:</strong> <span id="total">$0.00</span></p>

          <div class="d-grid gap-2 mt-3">
            <button id="btnCrear" class="btn btn-primary" form="formOrden">Crear Orden</button>
            <a href="../pacientes/pacientes.php" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </div>
      </div>

    </div>

    <!-- Form + estudios -->
    <div class="col-lg-8">
      <div class="card card-rounded shadow-sm">
        <div class="card-body">
          <h4 class="card-title mb-3">Crear Orden de Trabajo</h4>
          <p class="small-muted mb-3">Complete fecha/hora y seleccione uno o más estudios del catálogo.</p>

          <!-- Form -->
          <form id="formOrden" method="POST" action="guardarOrden.php">

            <input type="hidden" name="id_paciente" value="<?= $id_paciente ?>">

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Fecha de la cita</label>
                <input type="date" id="fecha" name="fecha_cita" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Hora de la cita</label>
                <select id="hora" name="hora_cita" class="form-select" required>
                  <option value="">Seleccione una hora</option>
                </select>
              </div>
            </div>

            <!-- Opcional: crear cita? si NO quieres que siempre exista, puedes agregar selector. -->
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="crearCita" name="crear_cita" checked>
              <label class="form-check-label" for="crearCita">Crear cita asociada a la orden</label>
            </div>

            <!-- Buscador -->
            <div class="mb-3">
              <label class="form-label">Buscar estudios (nombre / código)</label>
              <input id="buscarEstudio" class="form-control" placeholder="Escribe para filtrar la lista de estudios...">
            </div>

            <!-- Tabla de estudios (scroll) -->
            <div class="estudios-table mb-3 border rounded">
              <table class="table table-striped table-hover mb-0">
                <thead class="table-light sticky-top">
                  <tr>
                    <th style="width:48px"></th>
                    <th>Código</th>
                    <th>Estudio</th>
                    <th class="text-end">Precio</th>
                    <th class="text-center">Tipo</th>
                  </tr>
                </thead>
                <tbody id="tablaEstudios">
                  <?php foreach ($estudios as $e): ?>
                    <tr data-nombre="<?= htmlspecialchars(strtolower($e['nombre'])) ?>" data-codigo="<?= htmlspecialchars(strtolower($e['codigo'])) ?>">
                      <td class="align-middle text-center">
                        <input type="checkbox" class="form-check-input select-estudio" data-id="<?= $e['id_estudio'] ?>" data-precio="<?= $e['precio'] ?>">
                      </td>
                      <td class="align-middle"><?= htmlspecialchars($e['codigo']) ?></td>
                      <td class="align-middle"><?= htmlspecialchars($e['nombre']) ?></td>
                      <td class="align-middle text-end">$<?= number_format($e['precio'],2) ?></td>
                      <td class="align-middle text-center"><?= htmlspecialchars($e['tipo_resultado']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Aquí se agregan inputs ocultos con los estudios seleccionados antes de enviar -->
            <div id="inputsEstudios"></div>

          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap + jQuery (solo para comodidad visual; si no quieres jQuery puedo convertir a vanilla) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
  // inicializar fecha con hoy
  const fechaInput = document.getElementById('fecha');
  const hoy = new Date().toISOString().split('T')[0];
  fechaInput.value = hoy;

  // cargar horas (08:00 - 18:00). Si la fecha es hoy, solo mostrar horas posteriores a la hora actual + 1 (puedes ajustar)
  function cargarHoras() {
    const select = document.getElementById('hora');
    const fechaSeleccionada = fechaInput.value;
    const esHoy = fechaSeleccionada === new Date().toISOString().split('T')[0];
    const now = new Date();
    const horaActual = now.getHours();

    select.innerHTML = '<option value="">Seleccione una hora</option>';
    for (let h=8; h<=18; h++){
      // si es hoy, filtrar horas pasadas
      if (esHoy && h <= horaActual) continue;
      const hh = (h<10? '0'+h : h) + ':00';
      select.innerHTML += `<option value="${hh}">${hh}</option>`;
    }
  }

  fechaInput.addEventListener('change', cargarHoras);
  // se ejecuta al cargar
  cargarHoras();

  // FILTRAR tabla de estudios
  $('#buscarEstudio').on('input', function(){
    const q = $(this).val().toLowerCase().trim();
    $('#tablaEstudios tr').each(function(){
      const nombre = $(this).data('nombre') || '';
      const codigo = $(this).data('codigo') || '';
      const match = nombre.indexOf(q) !== -1 || codigo.indexOf(q) !== -1;
      $(this).toggle(match);
    });
  });

  // manejar selección y resumen
  function actualizarResumen(){
    const seleccionados = [];
    let total = 0;
    $('#tablaEstudios .select-estudio:checked').each(function(){
      const id = $(this).data('id');
      const precio = parseFloat($(this).data('precio')) || 0;
      // encontrar nombre y codigo desde la fila
      const tr = $(this).closest('tr');
      const nombre = tr.find('td').eq(2).text().trim();
      const codigo = tr.find('td').eq(1).text().trim();
      seleccionados.push({id, nombre, codigo, precio});
      total += precio;
    });

    // mostrar resumen
    const $res = $('#resumenLista');
    $res.empty();
    if (seleccionados.length === 0) {
      $res.html('<p class="small-muted">No hay estudios seleccionados</p>');
    } else {
      const ul = $('<ul class="list-group list-group-flush"></ul>');
      seleccionados.forEach(s => {
        ul.append(`<li class="list-group-item d-flex justify-content-between align-items-center py-1">${s.nombre} <span class="text-muted small">(${s.codigo})</span><strong>$${s.precio.toFixed(2)}</strong></li>`);
      });
      $res.append(ul);
    }
    $('#total').text('$' + total.toFixed(2));

    // preparar inputs ocultos para submit (reemplazar)
    const $inputs = $('#inputsEstudios');
    $inputs.empty();
    seleccionados.forEach(s => {
      $inputs.append(`<input type="hidden" name="estudios[]" value="${s.id}">`);
    });
  }

  // evento change checkbox
  $(document).on('change', '.select-estudio', function(){
    const tr = $(this).closest('tr');
    if (this.checked) tr.addClass('row-selected'); else tr.removeClass('row-selected');
    actualizarResumen();
  });

  // si el usuario da submit sin estudios, prevenir
  $('#formOrden').on('submit', function(e){
    if ($('.select-estudio:checked').length === 0) {
      e.preventDefault();
      // mostrar alerta bootstrap simple
      const alertBox = $('<div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">Seleccione al menos un estudio<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
      $(this).prepend(alertBox);
      $('html, body').animate({ scrollTop: $(this).offset().top - 100 }, 300);
      return false;
    }
    // de lo contrario se envía al servidor (guardarOrden.php)
    // puedes añadir validaciones extra aquí (hora seleccionada si crear_cita checked, etc.)
  });

})();
</script>

</body>
</html>
