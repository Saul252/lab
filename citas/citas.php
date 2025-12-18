<?php
// citas.php  (archivo único: muestra calendario + panel lateral + guarda citas)
session_start();
require "../conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
$estudios = $conexion->query("SELECT id_estudio, nombre, precio FROM estudios ORDER BY nombre ASC");

// -------------------- Guardar nueva cita (POST) --------------------
$mensaje = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'guardar_cita') {

    $id_paciente = intval($_POST['id_paciente'] ?? 0);
    $fecha_cita  = $_POST['fecha_cita'] ?? '';
    $hora_cita   = $_POST['hora_cita'] ?? '';
    $observ      = trim($_POST['observaciones'] ?? '');
    $estudiosSel = $_POST['estudios'] ?? [];

    if ($id_paciente <= 0 || !$fecha_cita || !$hora_cita || empty($estudiosSel)) {
        $mensaje = ['tipo'=>'error','texto'=>'Paciente, fecha, hora y al menos un estudio son obligatorios'];
    } else {

        $conexion->begin_transaction();

        try {
            /* 1️⃣ Crear cita */
            $stmt = $conexion->prepare(
                "INSERT INTO citas (id_paciente, fecha_cita, hora_cita, observaciones)
                 VALUES (?,?,?,?)"
            );
            $stmt->bind_param("isss", $id_paciente, $fecha_cita, $hora_cita, $observ);
            $stmt->execute();
            $id_cita = $stmt->insert_id;
            $stmt->close();

            /* 2️⃣ Crear orden */
            $folio = 'ORD-' . date('Ymd') . '-' . rand(1000,9999);

            $stmt = $conexion->prepare(
                "INSERT INTO ordenes (folio, id_paciente, id_cita)
                 VALUES (?,?,?)"
            );
            $stmt->bind_param("sii", $folio, $id_paciente, $id_cita);
            $stmt->execute();
            $id_orden = $stmt->insert_id;
            $stmt->close();

            /* 3️⃣ Insertar estudios */
            $total = 0;

            $stmtEst = $conexion->prepare(
                "SELECT precio FROM estudios WHERE id_estudio = ?"
            );

            $stmtIns = $conexion->prepare(
                "INSERT INTO orden_estudios (id_orden, id_estudio)
                 VALUES (?,?)"
            );

            foreach ($estudiosSel as $id_estudio) {
                $id_estudio = intval($id_estudio);

                $stmtEst->bind_param("i", $id_estudio);
                $stmtEst->execute();
                $precio = $stmtEst->get_result()->fetch_assoc()['precio'] ?? 0;

                $stmtIns->bind_param("ii", $id_orden, $id_estudio);
                $stmtIns->execute();

                $total += $precio;
            }

            $stmtEst->close();
            $stmtIns->close();

            /* 4️⃣ Actualizar total */
            $stmt = $conexion->prepare(
                "UPDATE ordenes SET total = ? WHERE id_orden = ?"
            );
            $stmt->bind_param("di", $total, $id_orden);
            $stmt->execute();
            $stmt->close();

            $conexion->commit();

            $mensaje = [
                'tipo'=>'success',
                'texto'=>"Cita y orden creadas correctamente (Folio: $folio)"
            ];

        } catch (Exception $e) {
            $conexion->rollback();
            $mensaje = ['tipo'=>'error','texto'=>'Error al guardar: '.$e->getMessage()];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'cambiar_estado') {

    $id_cita = intval($_POST['id_cita'] ?? 0);
    $estado  = $_POST['estado'] ?? '';

    if ($id_cita > 0 && in_array($estado, ['programada','completada','cancelada'])) {

        $stmt = $conexion->prepare("UPDATE citas SET estado = ? WHERE id_cita = ?");
        $stmt->bind_param("si", $estado, $id_cita);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}
// -------------------- Lecturas para mostrar en la página --------------------

// 1) Todas las citas (para FullCalendar y listado general)
$q = "SELECT c.id_cita, c.id_paciente, c.fecha_cita, c.hora_cita, c.estado, c.observaciones,
             p.nombre AS paciente
      FROM citas c
      JOIN pacientes p ON p.id_paciente = c.id_paciente
      ORDER BY c.fecha_cita ASC, c.hora_cita ASC";
$resCitas = $conexion->query($q);
$citasArr = [];
while ($r = $resCitas->fetch_assoc()) {
    $citasArr[] = $r;
}

// 2) Citas de hoy
$hoy = date('Y-m-d');
$stmtHoy = $conexion->prepare("SELECT c.id_cita, c.fecha_cita, c.hora_cita, c.estado, c.observaciones, p.nombre AS paciente
                               FROM citas c JOIN pacientes p ON p.id_paciente = c.id_paciente
                               WHERE c.fecha_cita = ? ORDER BY c.hora_cita ASC");
$stmtHoy->bind_param("s", $hoy);
$stmtHoy->execute();
$resHoy = $stmtHoy->get_result();
$citasHoyArr = $resHoy->fetch_all(MYSQLI_ASSOC);
$stmtHoy->close();

// 3) Lista breve (próximas 50)
$stmtList = $conexion->prepare("SELECT c.id_cita, c.fecha_cita, c.hora_cita, c.estado, p.nombre AS paciente
                                FROM citas c JOIN pacientes p ON p.id_paciente = c.id_paciente
                                ORDER BY c.fecha_cita DESC, c.hora_cita DESC LIMIT 50");
$stmtList->execute();
$resList = $stmtList->get_result();
$listaArr = $resList->fetch_all(MYSQLI_ASSOC);
$stmtList->close();

// 4) Pacientes para el select del modal (nombre + id)
$pac = $conexion->query("SELECT id_paciente, nombre FROM pacientes ORDER BY nombre ASC");

?>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lab/config.php'; // Configuración y rutas
require_once BASE_PATH . '/sidebar.php';                     // Componente sidebar

$paginaActual = 'Citas'; // Define la página actual
sidebar($paginaActual);         // Llama al sidebar
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Citas — Laboratorio</title>

    <link rel="stylesheet" href="/lab/css/style.css">
    <link rel="stylesheet" href="/lab/css/sidebar.css">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <!-- FullCalendar -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body { background:#f5f7fb; }
    .calendar-card { background:#fff; padding:18px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
    .side-card { background:#fff; padding:16px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.06); height:90vh; overflow:auto; }
    .cita-item { border-left:4px solid #0d6efd; background:#f8fbff; padding:10px; border-radius:6px; margin-bottom:10px; }
    .cita-empty { text-align:center; color:#6c757d; padding:18px; background:#fafafa; border-radius:6px; }
    .fc .fc-toolbar-title { font-weight:600; }
    /* compacto y legible */
    .small-muted { font-size:.9rem; color:#6c757d; }
  </style>
</head>
<body>
  <!-- NAV: (puedes reemplazar por tu nav existente; te devuelvo solo la barra simple aquí) -->
 


  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Gestión de Citas</h4>
      <div>
        <button class="btn btn-outline-secondary me-2" onclick="location.reload()">Actualizar</button>
        <button class="btn btn-primary" onclick="abrirModalNuevaCita()"><i class="bi bi-plus-lg"></i> Nueva Cita</button>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-9">
        <div class="calendar-card">
          <div id="calendar"></div>
        </div>
      </div>

      <div class="col-lg-3">
        <div class="side-card">
          <h6 class="mb-2">Citas de Hoy (<?php echo date('d/m/Y'); ?>)</h6>

          <div id="citasHoy">
            <?php if (count($citasHoyArr) === 0): ?>
              <div class="cita-empty">Sin citas el día de hoy</div>
            <?php else: ?>
              <?php foreach ($citasHoyArr as $c): ?>
                <div class="cita-item">
                  <div class="d-flex justify-content-between">
                    <div><strong><?= htmlspecialchars($c['paciente']) ?></strong></div>
                    <div class="small-muted"><?= substr($c['hora_cita'],0,5) ?></div>
                  </div>
                  <?php if (!empty($c['observaciones'])): ?>
                    <div class="small-muted mt-1"><?= htmlspecialchars($c['observaciones']) ?></div>
                  <?php endif; ?>
                  <div class="mt-2 d-flex gap-2">
                    <a class="btn btn-sm btn-outline-primary" href="editar_cita.php?id=<?= $c['id_cita'] ?>">Editar</a>
                    <a class="btn btn-sm btn-outline-danger" href="eliminar_cita.php?id=<?= $c['id_cita'] ?>"
                       onclick="return confirm('Eliminar esta cita?')">Eliminar</a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <hr>
          <h6 class="mb-2">Últimas 50 citas</h6>
          <div id="listaCitas">
            <?php if (count($listaArr) === 0): ?>
              <div class="cita-empty">No hay citas registradas</div>
            <?php else: ?>
              <?php foreach ($listaArr as $l): ?>
                <div class="mb-2">
                  <div class="d-flex justify-content-between">
                    <div><strong><?= htmlspecialchars($l['paciente']) ?></strong></div>
                    <div class="small-muted"><?= date('d/m', strtotime($l['fecha_cita'])) ?> <?= substr($l['hora_cita'],0,5) ?></div>
                  </div>
                  <div class="small-muted">Estado: <?= htmlspecialchars($l['estado']) ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: Nueva cita (se guarda por POST al mismo archivo) -->
  <div class="modal fade" id="modalCita" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="POST" action="">
        <input type="hidden" name="action" value="guardar_cita">
        <div class="modal-header">
          <h5 class="modal-title">Nueva cita</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
        <label class="form-label">Paciente</label>

<div class="input-group">
  <select name="id_paciente" id="selectPaciente" class="form-select" required>
    <option value="">-- Seleccione un paciente --</option>
    <?php
    $pac->data_seek(0);
    while ($pRow = $pac->fetch_assoc()):
    ?>
      <option value="<?= $pRow['id_paciente'] ?>">
        <?= htmlspecialchars($pRow['nombre']) ?>
      </option>
    <?php endwhile; ?>
  </select>

  <!-- 🔹 BOTÓN AGREGAR PACIENTE -->
  <button class="btn btn-outline-success"
          type="button"
          onclick="abrirModalPaciente()"
          title="Agregar nuevo paciente">
    <i class="bi bi-person-plus"></i>
  </button>
</div>

<small class="text-muted">
  ¿No aparece el paciente? Agrégalo aquí.
</small>

          <label class="form-label mt-3">Fecha</label>
          <input type="date" name="fecha_cita" class="form-control" required value="<?= date('Y-m-d') ?>">

          <label class="form-label mt-3">Hora</label>
          <input type="time" name="hora_cita" class="form-control" required>

          <label class="form-label mt-3">Observaciones</label>
          <textarea name="observaciones" class="form-control" rows="3"></textarea>
          <label class="form-label mt-3">Estudios</label>
<div class="border rounded p-2" style="max-height:200px; overflow:auto;">
  <?php while ($e = $estudios->fetch_assoc()): ?>
    <div class="form-check">
      <input class="form-check-input" type="checkbox"
             name="estudios[]" value="<?= $e['id_estudio'] ?>" id="est<?= $e['id_estudio'] ?>">
      <label class="form-check-label" for="est<?= $e['id_estudio'] ?>">
        <?= htmlspecialchars($e['nombre']) ?> ($<?= number_format($e['precio'],2) ?>)
      </label>
    </div>
  <?php endwhile; ?>
</div>

        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary">Guardar cita</button>
        </div>
      </form>
    </div>
  </div>
<!-- MODAL: Agregar Paciente -->
<div class="modal fade" id="modalPaciente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <form id="formPaciente" >

        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-person-plus"></i> Registrar Paciente
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nombre completo</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Edad</label>
              <input type="number" name="edad" class="form-control">
            </div>

            <div class="col-md-3">
              <label class="form-label">Sexo</label>
              <select name="sexo" class="form-select">
                <option value="H">Hombre</option>
                <option value="M">Mujer</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Domicilio</label>
            <input type="text" name="domicilio" class="form-control">
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Médico Solicitante</label>
            <input type="text" name="medico_solicitante" class="form-control">
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            Guardar Paciente
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

  <!-- SCRIPTS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Mensaje resultado POST (PHP -> JS)
    <?php if ($mensaje): ?>
      Swal.fire({
        icon: '<?php echo ($mensaje['tipo'] === 'success' ? 'success' : ($mensaje['tipo'] === 'warning' ? 'warning' : 'error')) ?>',
        title: <?php echo json_encode($mensaje['tipo'] === 'success' ? '¡Listo!' : ($mensaje['tipo'] === 'warning' ? 'Atención' : 'Error')) ?>,
        text: <?php echo json_encode($mensaje['texto']) ?>,
        timer: 2200,
        showConfirmButton: false
      }).then(()=> { window.location = location.pathname; });
    <?php endif; ?>

    // Inicializar FullCalendar con eventos desde PHP (array)
    const citas = <?php
        // convertir a array de eventos que FullCalendar entienda
$events = [];
foreach ($citasArr as $c) {

    // 🔹 color según estado
    switch ($c['estado']) {
        case 'completada':
            $color = '#198754'; // verde
            break;
        case 'cancelada':
            $color = '#dc3545'; // rojo
            break;
        default:
            $color = '#0d6efd'; // azul (programada)
    }

    $events[] = [
        'id' => $c['id_cita'],
        'title' => $c['paciente'],
        'start' => $c['fecha_cita'] . 'T' . substr($c['hora_cita'],0,5),
        'allDay' => false,

        // 👉 colores para FullCalendar
        'backgroundColor' => $color,
        'borderColor'     => $color,

        // 👉 datos extra (NO se rompen)
        'extendedProps' => [
            'hora'   => substr($c['hora_cita'],0,5),
            'observ' => $c['observaciones'] ?? '',
            'estado' => $c['estado']
        ]
    ];
}

        echo json_encode($events);
    ?>;

    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'es',
        height: 'auto',
        events: citas,
        eventClick: function(info) {
  const e = info.event;

  let html = `<strong>${e.title}</strong><br>${e.extendedProps.hora}`;
  if (e.extendedProps.observ) {
    html += `<br><small>${e.extendedProps.observ}</small>`;
  }
Swal.fire({
    title: 'Detalle de cita',
    html: html,

    input: 'select',
    inputOptions: {
        programada: 'Programada',
        completada: 'Completada',
        cancelada: 'Cancelada'
    },
    inputValue: e.extendedProps.estado,

    showCancelButton: true,
    confirmButtonText: 'Guardar estado',
    cancelButtonText: 'Cerrar',

    didOpen: () => {
        const actions = Swal.getActions();
        const confirmBtn = Swal.getConfirmButton();
        const cancelBtn  = Swal.getCancelButton();

        const editarBtn = document.createElement('a');
        editarBtn.href = `/lab/citas/accionesCitas/editarDetallesCita.php?id_cita=${e.extendedProps.id_cita ?? e.id}`;
        editarBtn.className = 'btn btn-lg  btn-primary mx-2';
        editarBtn.textContent = 'Editar cita';

        // INSERTAR ENTRE CONFIRMAR Y CANCELAR
        actions.insertBefore(editarBtn, cancelBtn);
    }
}).then((res) => {
    if (!res.isConfirmed) return;
    // guardar estado


    fetch('', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'cambiar_estado',
        id_cita: e.id,
        estado: res.value
      })
    })
    .then(r => r.json())
    .then(j => {
      if (j.ok) location.reload();
    });
  });
}
 });
      calendar.render();
    });

    function abrirModalNuevaCita(){
      new bootstrap.Modal(document.getElementById('modalCita')).show();
    }
  </script>
</body>
<script>
function abrirModalPaciente() {
  const modal = new bootstrap.Modal(
    document.getElementById('modalPaciente')
  );
  modal.show();
}
</script>
<script>
document.getElementById('formPaciente').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = this;
  const data = new FormData(form);

  fetch('/lab/pacientes/accionesPacientes.php/guadarPcienteajax.php', {
    method: 'POST',
    body: data
  })
  .then(r => r.json())
  .then(resp => {

    if (!resp.ok) {
      Swal.fire('Error', resp.msg, 'error');
      return;
    }

    // 🔄 Actualizar SELECT de pacientes
    const select = document.getElementById('selectPaciente');
    const option = document.createElement('option');
    option.value = resp.id;
    option.textContent = resp.nombre;
    option.selected = true;
    select.appendChild(option);

    // ✅ Cerrar modal
    bootstrap.Modal.getInstance(
      document.getElementById('modalPaciente')
    ).hide();

    form.reset();

    Swal.fire({
      icon: 'success',
      title: 'Paciente agregado',
      timer: 1400,
      showConfirmButton: false
    });

  })
  .catch(() => {
    Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
  });
});
</script>


</html>
