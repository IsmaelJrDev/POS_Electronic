<?php
// filepath: /home/silence/Documentos/GitHub/POS_Electronic/controller/productosController.php
require_once '../model/productoModel.php';

header('Content-Type: application/json');
$productos = ProductoModel::obtenerProductos();
echo json_encode($productos);
?>

