<?php
require_once '../config/database.php';
date_default_timezone_set('America/Mexico_City');

class VentaModel {
    public static function registrarVenta($productos, $total, $contacto) {
        global $conn;
        try {
            $conn->beginTransaction();

            // Insertar venta
            $stmt = $conn->prepare("INSERT INTO ventas (total, contacto) VALUES (:total, :contacto)");
            $stmt->execute([':total' => $total, ':contacto' => $contacto]);
            $venta_id = $conn->lastInsertId();

            foreach ($productos as $item) {
                // Validar stock
                $stmtStock = $conn->prepare("SELECT stock FROM productos WHERE id = :id FOR UPDATE");
                $stmtStock->execute([':id' => $item['id']]);
                $stock = $stmtStock->fetchColumn();
                            
                if ($stock === false || $stock < $item['cantidad']) {
                    $conn->rollBack();
                    return ['success' => false, 'message' => "No hay suficiente stock para el producto seleccionado."];
                }

                $stmtDetalle = $conn->prepare(
                "INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, fecha, telefono)
                VALUES (:venta_id, :producto_id, :cantidad, :precio, NOW(), :telefono)"
                );

                $stmtDetalle->execute([
                    ':venta_id' => $venta_id,
                    ':producto_id' => $item['id'],
                    ':cantidad' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':telefono' => $contacto // Aquí usas el número de teléfono
                ]);

                // Actualizar stock
                $stmtUpdate = $conn->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :id");
                $stmtUpdate->execute([
                    ':cantidad' => $item['cantidad'],
                    ':id' => $item['id']
                ]);
            }

            $conn->commit();
            // Aquí podrías enviar el ticket por correo/SMS usando $contacto
            return ['success' => true, 'venta_id' => $venta_id];
        } catch (Exception $e) {
            $conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function obtenerVentasTotales() {
        global $conn;
        $stmt = $conn->query("SELECT SUM(total) FROM ventas");
        return $stmt->fetchColumn() ?: 0;
    }

    public static function obtenerVentasDiarias() {
        global $conn;
        $stmt = $conn->query("SELECT DATE(fecha) as dia, SUM(total) as total FROM ventas GROUP BY dia ORDER BY dia DESC LIMIT 7");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>