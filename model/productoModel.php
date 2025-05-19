<?php
// filepath: /home/silence/Documentos/GitHub/POS_Electronic/model/productoModel.php
require_once '../config/database.php';

class ProductoModel {
    public static function obtenerProductos() {
        global $conn;
        $sql = "SELECT id, nombre, descripcion, precio, imagen, categoria FROM productos WHERE estado = 'activo' AND stock > 0";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function contarProductosCriticos($umbral = 5) {
        global $conn;
        $stmt = $conn->prepare("SELECT COUNT(*) FROM productos WHERE stock <= :umbral AND estado = 'activo'");
        $stmt->execute([':umbral' => $umbral]);
        return $stmt->fetchColumn();
    }

    public static function obtenerProductosCriticos($umbral = 3) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, nombre, stock FROM productos WHERE stock <= :umbral AND estado = 'activo'");
    $stmt->execute([':umbral' => $umbral]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerTodosLosProductos() {
        $db = self::getDB();
        $sql = "SELECT * FROM productos";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}

?>