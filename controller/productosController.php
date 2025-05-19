<?php
require_once '../model/productoModel.php';

header('Content-Type: application/json');

if (isset($_GET['todos'])) {
    // Todos los productos, sin filtrar por stock
    $productos = ProductoModel::obtenerTodosLosProductos();
    echo json_encode($productos);
} elseif (isset($_GET['compra'])) {
    // Productos para compra
    $productosCompra = ProductoModel::obtenerProductosCompra();
    echo json_encode($productosCompra);
} else {
    // Solo productos con stock > 0
    $productos = ProductoModel::obtenerProductos();
    echo json_encode($productos);
}
?>