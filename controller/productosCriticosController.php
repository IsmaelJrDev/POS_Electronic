<?php
require_once '../model/productoModel.php';
header('Content-Type: application/json');
$productos = ProductoModel::obtenerProductosCriticos();
echo json_encode($productos);
?>