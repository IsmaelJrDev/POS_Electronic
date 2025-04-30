<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "root", "SistemaPOS");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Variable para el descuento
$descuento = 0;
$totalVenta = 0;
$mensaje = '';

// Procesar eliminación de producto del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarProducto'])) {
    $productoID = intval($_POST['ProductoID']);

    $stmt = $conexion->prepare("CALL eliminarDelCarrito(?)");
    $stmt->bind_param("i", $productoID);
    if ($stmt->execute()) {
        $mensaje = "Producto eliminado del carrito.";
    } else {
        $mensaje = "Error al eliminar el producto.";
    }
    $stmt->close();

    // Recargar la página para reflejar los cambios
    echo "<script>window.location.href = window.location.href;</script>";
    exit;
}

// Si se realiza el pago y se ingresa el ID de la venta pasada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ventaPasadaId']) && !empty($_POST['ventaPasadaId'])) {
    $ventaPasadaId = intval($_POST['ventaPasadaId']);

    // Verificar si la venta pasada existe y obtener los puntos
    $stmt = $conexion->prepare("SELECT Puntos FROM puntos WHERE id_venta = ?");
    $stmt->bind_param("i", $ventaPasadaId);
    $stmt->execute();
    $stmt->store_result();

    // Comprobar si se encontraron filas
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($puntos);
        $stmt->fetch();

        // Usar la función MySQL para calcular el descuento
        $stmt2 = $conexion->prepare("SELECT calcular_descuento(?) AS descuento");
        $stmt2->bind_param("i", $puntos);
        $stmt2->execute();
        $stmt2->bind_result($descuento);
        $stmt2->fetch();
        $stmt2->close();

        // Eliminar los puntos de la venta pasada
        $stmt3 = $conexion->prepare("DELETE FROM puntos WHERE id_venta = ?");
        $stmt3->bind_param("i", $ventaPasadaId);
        $stmt3->execute();
        $stmt3->close();

        $mensaje = "Descuento aplicado con éxito. Descuento: $descuento%.";
    } else {
        // Si no se encontraron filas, el ID no existe o no tiene puntos
        echo "<script>alert('El ID ingresado ($ventaPasadaId) no existe o no tiene puntos.');</script>";
    }

    $stmt->close();
}



// Si se realiza la compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ventaPasadaId'])) {
    // Llamar al procedimiento almacenado para obtener los productos del carrito
    $stmt = $conexion->prepare("CALL obtener_productos_carrito()");
    $stmt->execute();
    $carritoResult = $stmt->get_result();

    // Liberar cualquier resultado anterior (en caso de que haya más de un conjunto de resultados)
    while ($conexion->more_results()) {
        $conexion->next_result();
    }

    if ($carritoResult->num_rows > 0) {
        // Iniciar transacción para asegurar la atomicidad
        $conexion->begin_transaction();

        try {
            // Asegurarse de que la fecha tenga el formato adecuado (YYYY-MM-DD)
            $fechaVenta = date('Y-m-d');
            // Preparar y ejecutar el procedimiento almacenado
            $stmt = $conexion->prepare("CALL registrar_venta(?, ?, @ventaId)");
            $stmt->bind_param("sd", $fechaVenta, $totalVenta);
            $stmt->execute();
            
            // Obtener el ID de la venta recién insertada
            $result = $conexion->query("SELECT @ventaId AS ventaId");
            if ($result && $row = $result->fetch_assoc()) {
                $ventaId = $row['ventaId'];
            }
            
            $stmt->close();

            // Insertar los productos vendidos en DetalleVentas
            while ($row = $carritoResult->fetch_assoc()) {
                $productoId = $row['ProductoID'];
                $nombre = $row['Nombre'];
                $precio = $row['Precio'];
                $cantidad = $row['Cantidad'];
                $foto = $row['foto'];
                $subtotal = $precio * $cantidad;

                // Llamar al procedimiento almacenado para insertar en DetalleVentas
                $stmt = $conexion->prepare("CALL insertar_detalle_venta(?, ?, ?, ?, ?)");
                $stmt->bind_param("iisid", $ventaId, $productoId, $foto, $cantidad, $subtotal);
                $stmt->execute();
                $stmt->close();
            }

            // Calcular el total de la venta usando la función MySQL
            $stmt4 = $conexion->prepare("SELECT calcular_total_venta(?) AS totalVenta");
            $stmt4->bind_param("i", $ventaId);  // Asegúrate de usar la variable PHP $ventaId
            $stmt4->execute();
            $stmt4->bind_result($totalVenta);
            $stmt4->fetch();
            $stmt4->close();

            // Aplicar descuento basado en los puntos
            $totalVenta -= $totalVenta * ($descuento / 100);

            // Llamar al procedimiento almacenado para actualizar el total de la venta
            $stmt = $conexion->prepare("CALL actualizar_total_venta(?, ?)");
            $stmt->bind_param("id", $ventaId, $totalVenta);  
            $stmt->execute();
            $stmt->close();

            // Llamar al procedimiento almacenado para eliminar los productos del carrito
            $stmt = $conexion->prepare("CALL eliminar_productos_carrito()");
            $stmt->execute();
            $stmt->close();

            // Confirmar la transacción
            $conexion->commit();

            $mensaje = "Compra realizada con éxito. Total a pagar: $" . number_format($totalVenta, 2);
        } catch (Exception $e) {
            // En caso de error, revertir la transacción
            $conexion->rollback();
            $mensaje = "Error en la compra: " . $e->getMessage();
        }
    } else {
        $mensaje = "Ingrese por lo menos un producto";
    }
}


// Inicializar la variable $totalCarrito
$totalCarrito = 0;
// Llamar al procedimiento almacenado para obtener los productos del carrito
$stmt = $conexion->prepare("CALL obtener_productos_carrito()");
$stmt->execute();
$result = $stmt->get_result();  // Obtener los resultados del procedimiento 
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos generales */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .contenedor {
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: left;
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .btn-principal {
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .btn-principal:hover {
            background-color: #2ecc71;
        }

        /* Estilos de la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #27ae60;
            color: white;
        }

        .boton-eliminar {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .boton-eliminar:hover {
            background-color: darkred;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
        }

        /* Estilo para mensajes */
        .mensaje-compra {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        /* Estilos del formulario para ID de venta pasada y el botón */
        .form-venta {
            margin-top: 20px;
            background-color: #f2f2f2;
            padding: 15px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-venta input[type="number"] {
            padding: 8px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 50%;
        }

        .boton-pagar {
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .boton-pagar:hover {
            background-color: #2ecc71;
        }
        h1 {
            text-align: center;  
            color: #2c3e50;
            font-size: 32px;  
            margin-bottom: 20px;
        }

    </style>
</head>
<body>

<div class="contenedor">
    <a href="producto.php" class="btn-principal">Productos</a>

    <h1>Carrito de Compras</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje-compra">
            <p><?php echo $mensaje; ?></p>
        </div>
    <?php endif; ?>

    <!-- Tabla de productos del carrito -->
    <table>
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Conexión a la base de datos y obtener los productos del carrito
            $conexion = new mysqli("localhost", "root", "root", "SistemaPOS");
            if ($conexion->connect_error) {
                die("Conexión fallida: " . $conexion->connect_error);
            }

            $stmt = $conexion->prepare("CALL obtener_productos_carrito()");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $totalCarrito = 0;
                while ($row = $result->fetch_assoc()) {
                    $subtotal = $row['Precio'] * $row['Cantidad'];
                    $totalCarrito += $subtotal;
                    ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($row['foto']); ?>" alt="<?php echo htmlspecialchars($row['Nombre']); ?>" width="100"></td>
                        <td><?php echo htmlspecialchars($row['Nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['Cantidad']); ?></td>
                        <td>$<?php echo number_format($subtotal, 2); ?></td>
                        <td>
                            <!-- Formulario para eliminar el producto -->
                            <form method="POST" action="">
                                <input type="hidden" name="ProductoID" value="<?php echo $row['ProductoID']; ?>">
                                <button type="submit" name="eliminarProducto" class="boton-eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='5'>No hay productos en el carrito.</td></tr>";
            }

            $stmt->close();
            $conexion->close();
            ?>
        </tbody>
    </table>

    <p class="total">Total a pagar: $<?php echo number_format($totalCarrito, 2); ?></p>

    <!-- Formulario para ingresar ID de venta pasada y realizar la venta -->
    <div class="form-venta">
        <form method="POST" action="">
            <label for="ventaPasadaId">ID de venta pasada (para aplicar descuento):</label>
            <input type="number" id="ventaPasadaId" name="ventaPasadaId" placeholder="Ingresa ID de la venta pasada">
            <button type="submit" class="boton-pagar">Realizar Venta</button>
        </form>
    </div>
</div>

</body>
</html>