<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "root", "SistemaPOS");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Procesar eliminación de producto del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarProducto'])) {
    $productoID = intval($_POST['ProductoID']);

    $stmt = $conexion->prepare("CALL eliminarDelCarrito(?)");
    $stmt->bind_param("i", $productoID);
    if ($stmt->execute()) {
        echo "<script>alert('Producto eliminado del carrito.');</script>";
    } else {
        echo "<script>alert('Error al eliminar el producto.');</script>";
    }
    $stmt->close();

    echo "<script>window.location.href = window.location.href;</script>";
    exit;
}

// Función para finalizar la compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizarCompra'])) {
    // Verificar si hay productos en el carrito
    $stmt = $conexion->prepare("SELECT COUNT(*) AS totalProductos FROM carrito");
    $stmt->execute();
    $stmt->bind_result($totalProductos);
    $stmt->fetch();
    $stmt->close();

    if ($totalProductos == 0) {
        echo "<script>alert('No hay ningún producto en el carrito.');</script>";
    } else {
        $idVentaAnterior = isset($_POST['idVentaAnterior']) ? intval($_POST['idVentaAnterior']) : 0;
        $descuento = 0;

        if ($idVentaAnterior > 0) {
            $stmt = $conexion->prepare("SELECT Puntos FROM puntos WHERE id_venta = ?");
            $stmt->bind_param("i", $idVentaAnterior);
            $stmt->execute();
            $stmt->bind_result($puntos);
            $stmt->fetch();
            $stmt->close();

            if ($puntos == 10) {
                $descuento = 10;
            } else {
                echo "<script>alert('Esta venta no cuenta con puntos.');</script>";
            }

            $stmt = $conexion->prepare("CALL CrearVentaConEliminacionDePuntos(?)");
            $stmt->bind_param("i", $idVentaAnterior);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conexion->prepare("CALL crearVenta(@idVenta)");
        $stmt->execute();
        $resultado = $conexion->query("SELECT @idVenta AS idVenta");
        $row = $resultado->fetch_assoc();
        $idVenta = $row['idVenta'];
        $stmt->close();

        $stmt = $conexion->prepare("CALL obtenerProductosCarrito()");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        $total = 0;
        while ($fila = $resultado->fetch_assoc()) {
            $subtotal = $fila['Cantidad'] * $fila['Precio'];
            $subtotalConDescuento = $subtotal * ((100 - $descuento) / 100);
            $total += $subtotalConDescuento;

            $stmt = $conexion->prepare("CALL insertarDetalleVenta(?, ?, ?, ?, ?)");
            $stmt->bind_param("isidd", $idVenta, $fila['foto'], $fila['ProductoID'], $fila['Cantidad'], $subtotalConDescuento);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conexion->prepare("CALL actualizarTotalVenta(?, ?)");
        $stmt->bind_param("id", $idVenta, $total);
        $stmt->execute();
        $stmt->close();

        $stmt = $conexion->prepare("CALL VaciarCarrito()");
        $stmt->execute();
        $stmt->close();

        echo "
        <script>
            alert('Compra finalizada con éxito. Total: $" . number_format($total, 2) . " (Descuento aplicado: $descuento%)');
        </script>";
    }
}

$stmt = $conexion->prepare("CALL obtenerProductosDelCarrito()");
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .contenedor {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .btn-regresar {
            display: block;
            width: fit-content;
            margin: 10px 0;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-regresar:hover {
            background-color:rgb(32, 104, 49);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
            color: #333;
        }

        td img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .btn-eliminar {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-eliminar:hover {
            background-color:rgb(155, 48, 36);
        }

        label {
            font-size: 16px;
            margin-right: 10px;
        }

        input[type="number"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 150px;
            margin-bottom: 10px;
        }

        .btn-finalizar {
            display: block;
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .btn-finalizar:hover {
            background-color:rgb(43, 104, 46);
        }

        @media (max-width: 768px) {
            table, th, td {
                font-size: 14px;
            }

            input[type="number"] {
                width: 100%;
            }

            .btn-finalizar {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h1>Carrito de Compras</h1>

        <!-- Botón para regresar a productos -->
        <a href="producto.php" class="btn-regresar">Volver a los productos</a>

        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    if ($resultado->num_rows > 0) {
                        while ($fila = $resultado->fetch_assoc()) {
                            $subtotal = $fila['Cantidad'] * $fila['Precio'];
                            $total += $subtotal;
                            echo "<tr>";
                            echo "<td><img src='" . $fila['foto'] . "' alt='" . $fila['Nombre'] . "'></td>";
                            echo "<td>" . $fila['Nombre'] . "</td>";
                            echo "<td>$" . number_format($fila['Precio'], 2) . "</td>";
                            echo "<td>" . $fila['Cantidad'] . "</td>";
                            echo "<td>$" . number_format($subtotal, 2) . "</td>";
                            echo "<td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='ProductoID' value='" . $fila['ProductoID'] . "'>
                                        <button type='submit' name='eliminarProducto' class='btn-eliminar'>Eliminar</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No hay productos en el carrito.</td></tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">Total</th>
                        <th>$<?php echo number_format($total, 2); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
            <label for="idVentaAnterior">ID de Venta Anterior:</label>
            <input type="number" name="idVentaAnterior" id="idVentaAnterior" min="0" placeholder="Ingrese ID de Venta">
            <button type="submit" name="finalizarCompra" class="btn-finalizar">Finalizar Compra</button>
        </form>
    </div>
</body>
</html>

<?php
$conexion->close();
?>
