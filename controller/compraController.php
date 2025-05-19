<?php
require_once '../config/database.php';

header('Content-Type: application/json');

define('MAX_STOCK', 60); // Cambia este valor si tu máximo es diferente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = $_POST['producto_id'] ?? null;
    $cantidad = $_POST['cantidad'] ?? null;
    $precio = $_POST['precio'] ?? null;

    if ($producto_id && $cantidad && $precio) {
        global $conn;
        // Consulta el stock actual
        $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = :id");
        $stmt->execute([':id' => $producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            $nuevo_stock = $producto['stock'] + $cantidad;
            if ($nuevo_stock > MAX_STOCK) {
                echo json_encode(['success' => false, 'message' => 'No puedes exceder el stock máximo de ' . MAX_STOCK]);
                exit;
            }
            // Actualiza el stock del producto
            $stmt = $conn->prepare("UPDATE productos SET stock = :stock WHERE id = :id");
            $stmt->execute([':stock' => $nuevo_stock, ':id' => $producto_id]);
            // (Opcional) Puedes guardar la compra en una tabla de compras aquí
            echo json_encode(['success' => true, 'message' => 'Compra registrada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    }
}
?>