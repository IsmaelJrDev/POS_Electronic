<?php
// filepath: /home/silence/Documentos/GitHub/POS_Electronic/model/productoModel.php
require_once '../config/database.php';

class ProductoModel {
    public static function obtenerProductos() {
        global $conn;
        $sql = "SELECT id, nombre, descripcion, precio, imagen FROM productos WHERE estado = 'activo' AND stock > 0";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>