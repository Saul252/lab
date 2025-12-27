<?php
session_start();
require "../../conexion.php";

if (!isset($_SESSION["usuario"])) {
    exit("Acceso denegado");
}

$filtro = $_GET["filtro"] ?? "";
$valor  = $_GET["valor"] ?? "";

/* ===============================
   VALIDAR VALOR (MISMO QUE CAJA)
   =============================== */
$valorSeguro = null;

if ($filtro === "dia" && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
    $valorSeguro = $valor;
}
if ($filtro === "semana" && preg_match('/^\d{4}-W\d{2}$/', $valor)) {
    $valorSeguro = $valor;
}
if ($filtro === "mes" && preg_match('/^\d{4}-\d{2}$/', $valor)) {
    $valorSeguro = $valor;
}
if ($filtro === "anio" && preg_match('/^\d{4}$/', $valor)) {
    $valorSeguro = $valor;
}

/* ===============================
   WHERE
   =============================== */
$where = "WHERE 1=1";

if ($filtro === "dia" && $valorSeguro) {
    $where .= " AND DATE(p.fecha_pago) = '$valorSeguro'";
}

if ($filtro === "semana" && $valorSeguro) {
    $anio   = substr($valorSeguro, 0, 4);
    $semana = substr($valorSeguro, 6);
    $where .= " AND YEAR(p.fecha_pago) = '$anio'
                AND WEEK(p.fecha_pago, 1) = '$semana'";
}

if ($filtro === "mes" && $valorSeguro) {
    $where .= " AND DATE_FORMAT(p.fecha_pago, '%Y-%m') = '$valorSeguro'";
}

if ($filtro === "anio" && $valorSeguro) {
    $where .= " AND YEAR(p.fecha_pago) = '$valorSeguro'";
}

/* ===============================
   CONSULTA
   =============================== */
$sql = "
SELECT 
    p.id_pago,
    o.folio,
    pa.nombre AS paciente,
    p.metodo,
    p.monto,
    p.fecha_pago
FROM pagos p
INNER JOIN ordenes o ON o.id_orden = p.id_orden
INNER JOIN pacientes pa ON pa.id_paciente = o.id_paciente
$where
ORDER BY p.fecha_pago DESC
";

$res = $conexion->query($sql);

/* ===============================
   CABECERAS EXCEL
   =============================== */
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=corte_caja.xls");
header("Pragma: no-cache");
header("Expires: 0");

/* ===============================
   SALIDA
   =============================== */
echo "<table border='1'>";
echo "<tr>
        <th>ID Pago</th>
        <th>Folio</th>
        <th>Paciente</th>
        <th>Método</th>
        <th>Monto</th>
        <th>Fecha Pago</th>
      </tr>";

$total = 0;

while ($row = $res->fetch_assoc()) {
    $total += $row["monto"];

    echo "<tr>
            <td>{$row['id_pago']}</td>
            <td>{$row['folio']}</td>
            <td>{$row['paciente']}</td>
            <td>{$row['metodo']}</td>
            <td>{$row['monto']}</td>
            <td>{$row['fecha_pago']}</td>
          </tr>";
}

echo "<tr>
        <td colspan='4'><strong>TOTAL</strong></td>
        <td colspan='2'><strong>$" . number_format($total, 2) . "</strong></td>
      </tr>";

echo "</table>";
exit;
