<?php
// citas.php  (archivo único: muestra calendario + panel lateral + guarda citas)
session_start();
require "../conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

// -------------------- Guardar nueva cita (POST) --------------------
$mensaje = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'guardar_cita') {
    $id_paciente = intval($_POST['id_paciente'] ?? 0);
    $fecha_cita  = $_POST['fecha_cita'] ?? '';
    $hora_cita   = $_POST['hora_cita'] ?? '';
    $observ      = trim($_POST['observaciones'] ?? '');

    if ($id_paciente <= 0 || !$fecha_cita || !$hora_cita) {
        $mensaje = ['tipo' => 'error', 'texto' => 'Faltan datos obligatorios para crear la cita.'];
    } else {
        // Opcional: validar conflicto de horario (misma fecha y hora)
        $stmt = $conexion->prepare("SELECT COUNT(*) AS cnt FROM citas WHERE fecha_cita = ? AND hora_cita = ?");
        $stmt->bind_param("ss", $fecha_cita, $hora_cita);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($r && intval($r['cnt']) > 0) {
            $mensaje = ['tipo' => 'warning', 'texto' => 'Ya existe otra cita en esa fecha/hora.'];
        } else {
            $ins = $conexion->prepare("INSERT INTO citas (id_paciente, fecha_cita, hora_cita, observaciones) VALUES (?, ?, ?, ?)");
            $ins->bind_param("isss", $id_paciente, $fecha_cita, $hora_cita, $observ);
            if ($ins->execute()) {
                $mensaje = ['tipo' => 'success', 'texto' => 'Cita creada correctamente.'];
            } else {
                $mensaje = ['tipo' => 'error', 'texto' => 'Error al guardar la cita: ' . $conexion->error];
            }
            $ins->close();
        }
    }
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
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Citas — Laboratorio</title>

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
 
<nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">

            <button class="btn btn-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">



                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white" class="bi bi-list"
                    viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M2.5 12.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm0-4a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11z" />
                </svg>

                Menu
            </button>

            <div class="offcanvas offcanvas-start bg-dark" data-bs-scroll="true" tabindex="-1"
                id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">

                <div class="offcanvas-header">
                    <h5 class="offcanvas-title text-white bg-dark" id="offcanvasWithBothOptionsLabel">Menu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>

                <div class="offcanvas-body">

                    <?php if ($_SESSION["rol"] == "admin") { ?>

                    <div class="d-flex flex-column p-3 text-white bg-dark" style="height: 90vh; width: 100%;">

                        <ul class="nav nav-pills flex-column mb-auto">

                            <li class="nav-item">
                                <a href="/lab/bienvenida.php" class="nav-link text-white  hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 9l9-6 9 6"></path>
                                        <path d="M9 22V12h6v10"></path>
                                        <path d="M3 9v12h18V9"></path>
                                    </svg>

                                    Inicio
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/lab/dashboard/dashboard.php" class="nav-link text-white  hoverbutton">
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
                                <a href="/lab/usuarios/administarUsuarios.php" class="nav-link text-white hoverbutton">
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
                                <a href="/lab/pacientes/pacientes.php" class="nav-link text-white hoverbutton">
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
                                <a href="/lab/citas/citas.php" class="nav-link text-white active hoverbutton">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <path d="M16 2v4M8 2v4M3 10h18"></path>
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

            <span class="navbar-brand">Almacen</span>

            <div class="dropdown me-4">
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
    </nav>
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
          <select name="id_paciente" class="form-select" required>
            <option value="">-- Seleccione --</option>
            <?php while ($pRow = $pac->fetch_assoc()): ?>
              <option value="<?= $pRow['id_paciente'] ?>"><?= htmlspecialchars($pRow['nombre']) ?></option>
            <?php endwhile; ?>
          </select>

          <label class="form-label mt-3">Fecha</label>
          <input type="date" name="fecha_cita" class="form-control" required value="<?= date('Y-m-d') ?>">

          <label class="form-label mt-3">Hora</label>
          <input type="time" name="hora_cita" class="form-control" required>

          <label class="form-label mt-3">Observaciones</label>
          <textarea name="observaciones" class="form-control" rows="3"></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary">Guardar cita</button>
        </div>
      </form>
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
            $events[] = [
                'id' => $c['id_cita'],
                'title' => $c['paciente'],
                'start' => $c['fecha_cita'] . 'T' . substr($c['hora_cita'],0,5),
                'allDay' => false,
                'extendedProps' => ['hora' => substr($c['hora_cita'],0,5), 'observ' => $c['observaciones'] ?? '', 'estado' => $c['estado']]
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
          if (e.extendedProps.observ) html += `<br><small>${e.extendedProps.observ}</small>`;
          Swal.fire({
            title: 'Detalle de cita',
            html: html,
            showCancelButton: true,
            confirmButtonText: 'Editar',
            cancelButtonText: 'Cerrar'
          }).then((res)=>{
            if (res.isConfirmed) {
              // redirigir a editar (debes tener editar_cita.php)
              window.location = 'editar_cita.php?id=' + e.id;
            }
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
</html>
