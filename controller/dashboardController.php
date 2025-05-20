<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../model/productoModel.php';
require_once '../model/ventaModel.php';

header('Content-Type: application/json');

$criticos = ProductoModel::contarProductosCriticos();
$ventasTotales = VentaModel::obtenerVentasTotales();
$ventasDiarias = VentaModel::obtenerVentasDiarias();

// Calcular ventas del día actual
$ventasHoy = 0;
$hoy = date('Y-m-d');
foreach ($ventasDiarias as $venta) {
    if (trim($venta['dia']) == $hoy) {
        $ventasHoy = (float)$venta['total'];
        break;
    }
}

echo json_encode([
    'productosCriticos' => $criticos,
    'ventasTotales' => $ventasTotales,
    'ventasDiarias' => $ventasDiarias,
    'ventasHoy' => $ventasHoy
]);
?>