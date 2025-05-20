<?php
require_once '../model/ventaModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$productos = $data['productos'] ?? [];
$total = $data['total'] ?? 0;
$contacto = $data['contacto'] ?? '';

$resultado = VentaModel::registrarVenta($productos, $total, $contacto);
header('Content-Type: application/json');
echo json_encode($resultado);
?>