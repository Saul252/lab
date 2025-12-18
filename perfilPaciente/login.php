<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Consulta de Resultados | Laboratorio</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Fuente elegante -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

<style>
/* ===== RESET ===== */
body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    overflow: hidden;
}

/* ===== FONDO ANIMADO ===== */
.bg-animated {
    position: fixed;
    inset: 0;
    background: linear-gradient(
        120deg,
        #0d6efd,
        #20c997,
        #6610f2,
        #0dcaf0
    );
    background-size: 400% 400%;
    animation: gradientMove 12s ease infinite;
    z-index: -2;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* ===== EFECTO CRISTAL ===== */
.glass-card {
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.25);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

/* ===== TITULO ===== */
.title {
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* ===== INPUTS ===== */
.form-control {
    border-radius: 10px;
    padding: 10px 14px;
    border: none;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(13,110,253,.25);
}

/* ===== BOTÓN ===== */
.btn-primary {
    border-radius: 10px;
    padding: 10px;
    font-weight: 600;
}

/* ===== TEXTO SUAVE ===== */
.subtle {
    font-size: 13px;
    color: rgba(255,255,255,0.75);
}

/* ===== FOOTER ===== */
.footer {
    margin-top: 16px;
    font-size: 12px;
    color: rgba(255,255,255,0.7);
    text-align: center;
}
</style>
</head>

<body>

<!-- Fondo animado -->
<div class="bg-animated"></div>

<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh">
    <div class="glass-card p-4" style="max-width:420px; width:100%">

        <div class="text-center mb-4 text-white">
            <div style="font-size:40px">🧪</div>
            <h4 class="title mt-2">Resultados de Laboratorio</h4>
            <div class="subtle">Consulta segura de estudios clínicos</div>
        </div>

        <form method="POST" action="validar/validar.php">
            <div class="mb-3">
                <label class="form-label text-white">Correo electrónico</label>
                <input type="email" name="email" class="form-control"
                       placeholder="paciente@email.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-white">Folio</label>
                <input type="text" name="folio" class="form-control"
                       placeholder="Ej. LAB-2024-001" required>
            </div>

            <button class="btn btn-primary w-100">
                🔍 Consultar resultados
            </button>
        </form>

        <div class="footer">
            Acceso confidencial • Laboratorio Clínico
        </div>

    </div>
</div>

</body>
</html>
