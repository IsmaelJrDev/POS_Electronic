<?php
// Iniciar sesión para acceder al correo del usuario
session_start();
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conexion = new mysqli("localhost", "root", "root", "SistemaPOS");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$mensaje = '';
$totalVenta = 0;
$iva = 0.16; // IVA 16%

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminarProducto'])) {
    $productoID = intval($_POST['ProductoID']);

    // Obtener cantidad del carrito
    $stmt = $conexion->prepare("SELECT Cantidad FROM carrito WHERE ProductoID = ?");
    $stmt->bind_param("i", $productoID);
    $stmt->execute();
    $stmt->bind_result($cantidadEnCarrito);
    if ($stmt->fetch()) {
        $stmt->close();

        // Devolver stock
        $stmt = $conexion->prepare("UPDATE productos SET Stock = Stock + ? WHERE ProductoID = ?");
        $stmt->bind_param("ii", $cantidadEnCarrito, $productoID);
        $stmt->execute();
        $stmt->close();

        // Eliminar del carrito
        $stmt = $conexion->prepare("DELETE FROM carrito WHERE ProductoID = ?");
        $stmt->bind_param("i", $productoID);
        $stmt->execute();
        $stmt->close();

        $mensaje = "Producto eliminado del carrito y devuelto al stock.";
    } else {
        $stmt->close();
        $mensaje = "No se encontró el producto en el carrito.";
    }

    echo "<script>window.location.href = window.location.href;</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['realizarVenta'])) {
    // Obtener correo del usuario desde la sesión
    $correoCliente = isset($_SESSION['correo']) ? $_SESSION['correo'] : 'cliente@ejemplo.com'; // Valor por defecto si no está en sesión

    $carritoResult = $conexion->query("SELECT c.ProductoID, p.Nombre, p.Precio, c.Cantidad, p.foto 
        FROM carrito c 
        JOIN productos p ON c.ProductoID = p.ProductoID");

    if ($carritoResult->num_rows > 0) {
        $conexion->begin_transaction();
        try {
            $fechaVenta = date('Y-m-d');
            $stmt = $conexion->prepare("INSERT INTO ventas (Fecha, Total) VALUES (?, 0)");
            $stmt->bind_param("s", $fechaVenta);
            $stmt->execute();
            $ventaId = $stmt->insert_id;
            $stmt->close();

            while ($row = $carritoResult->fetch_assoc()) {
                $productoId = $row['ProductoID'];
                $precio = $row['Precio'];
                $cantidad = $row['Cantidad'];
                $foto = $row['foto'];
                $subtotal = $precio * $cantidad;

                $stmt = $conexion->prepare("INSERT INTO DetalleVentas (id_venta, ProductoID, foto, Cantidad, Subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisid", $ventaId, $productoId, $foto, $cantidad, $subtotal);
                $stmt->execute();
                $stmt->close();
            }

            $result = $conexion->query("SELECT SUM(Subtotal) AS total FROM DetalleVentas WHERE id_venta = $ventaId");
            $row = $result->fetch_assoc();
            $totalVenta = $row['total'];

            // Calcular IVA
            $totalConIva = $totalVenta * (1 + $iva); // Total + IVA

            $stmt = $conexion->prepare("UPDATE ventas SET Total = ? WHERE id_venta = ?");
            $stmt->bind_param("di", $totalConIva, $ventaId);
            $stmt->execute();
            $stmt->close();

            $conexion->query("DELETE FROM carrito");

            $conexion->commit();
            $mensaje = "Compra realizada con éxito. Total a pagar (con IVA): $" . number_format($totalConIva, 2);

            // Enviar correo al cliente
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP de Gmail
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'lia.abril.azamar.hernandez@gmail.com'; // Tu correo de Gmail
                $mail->Password = 'kzcg read pgkn xehu'; // Tu contraseña de Gmail o contraseña de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Remitente y destinatario
                $mail->setFrom('lia_Abril', 'Computec');
                $mail->addAddress($correoCliente); // Dirección del cliente

                // Iniciar el cuerpo del correo
                $mail->isHTML(true);
                $mail->Subject = 'Detalles de tu compra';
                $mail->Body = "Gracias por tu compra. Aquí están los detalles de tu compra:<br><br>";

                // Consultar los productos comprados en la venta
                $detalleVentasResult = $conexion->query("
                    SELECT dv.Cantidad, p.Nombre, p.Precio 
                    FROM detalleventas dv
                    JOIN productos p ON dv.ProductoID = p.ProductoID
                    WHERE dv.id_venta = '$ventaId'");

                // Verificamos si hay productos en la venta
                if ($detalleVentasResult->num_rows > 0) {
                    // Crear la tabla de productos comprados
                    $mail->Body .= "<h3>Productos comprados:</h3>";
                    $mail->Body .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
                    $mail->Body .= "<tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>";

                    // Iteramos sobre los productos y agregamos cada uno a la tabla
                    while ($row = $detalleVentasResult->fetch_assoc()) {
                        $subtotal = $row['Cantidad'] * $row['Precio'];
                        $mail->Body .= "<tr>";
                        $mail->Body .= "<td>" . $row['Nombre'] . "</td>";
                        $mail->Body .= "<td>" . $row['Cantidad'] . "</td>";
                        $mail->Body .= "<td>$" . number_format($row['Precio'], 2) . "</td>";
                        $mail->Body .= "<td>$" . number_format($subtotal, 2) . "</td>";
                        $mail->Body .= "</tr>";
                    }

                    // Cerrar la tabla
                    $mail->Body .= "</table><br><br>";
                } else {
                    // Si no hay productos en DetalleVentas para la venta
                    $mail->Body .= "No se encontraron productos para esta venta.<br><br>";
                }

                // Calcular el IVA y el total con IVA
                $ivaAmount = $totalVenta * $iva;
                $totalConIva = $totalVenta + $ivaAmount;

                // Agregar el total, IVA y total con IVA
                $mail->Body .= "Total: $" . number_format($totalVenta, 2) . "<br>";
                $mail->Body .= "IVA (16%): $" . number_format($ivaAmount, 2) . "<br>";
                $mail->Body .= "Total con IVA: $" . number_format($totalConIva, 2);

                // Enviar el correo
                $mail->send();
            } catch (Exception $e) {
                $mensaje .= "<br> Error al enviar el correo: " . $mail->ErrorInfo;
            }

        } catch (Exception $e) {
            $conexion->rollback();
            $mensaje = "Error en la compra: " . $e->getMessage();
        }
    } else {
        $mensaje = "Ingrese por lo menos un producto";
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        .contenedor {
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 32px;
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

        .mensaje-compra {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .form-venta {
            text-align: center;
            margin-top: 20px;
        }

        .boton-pagar {
            background-color: #27ae60;
            color: white;
            padding: 10px 25px;
            font-size: 18px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .boton-pagar:hover {
            background-color: #2ecc71;
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
            $carritoResult = $conexion->query("SELECT c.ProductoID, p.Nombre, p.Precio, c.Cantidad, p.foto 
                FROM carrito c 
                JOIN productos p ON c.ProductoID = p.ProductoID");

            if ($carritoResult->num_rows > 0) {
                while ($row = $carritoResult->fetch_assoc()) {
                    $subtotal = $row['Precio'] * $row['Cantidad'];
                    echo "<tr>";
                    echo "<td><img src='{$row['foto']}' alt='Imagen Producto' width='50'></td>";
                    echo "<td>{$row['Nombre']}</td>";
                    echo "<td>{$row['Cantidad']}</td>";
                    echo "<td>$" . number_format($subtotal, 2) . "</td>";
                    echo "<td><form method='POST'>
                            <input type='hidden' name='ProductoID' value='{$row['ProductoID']}'>
                            <button type='submit' name='eliminarProducto' class='boton-eliminar'>Eliminar</button>
                          </form></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay productos en el carrito.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="total">
    <p>Total: $
        <?php
        $totalVenta = 0;
        $carritoResult = $conexion->query("SELECT c.Cantidad, p.Precio FROM carrito c 
            JOIN productos p ON c.ProductoID = p.ProductoID");

        while ($row = $carritoResult->fetch_assoc()) {
            $totalVenta += $row['Cantidad'] * $row['Precio'];
        }
        echo number_format($totalVenta, 2);
        ?>
    </p>

    <p>IVA (16%): $
        <?php
        $ivaAmount = $totalVenta * $iva;
        echo number_format($ivaAmount, 2);
        ?>
    </p>

    <p>Total con IVA: $
        <?php
        $totalConIva = $totalVenta + $ivaAmount;
        echo number_format($totalConIva, 2);
        ?>
    </p>
    </div>


    <form class="form-venta" method="POST">
        <button type="submit" name="realizarVenta" class="boton-pagar">Realizar Compra</button>
    </form>
</div>

</body>
</html>

<?php
$conexion->close();
?>
