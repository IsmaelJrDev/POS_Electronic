<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "root", "SistemaPOS");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$mensaje = null;
$ventas = [];
$detalleVentas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mostrar Ventas
    if (isset($_POST['mostrarVentas'])) {
        $sql = "SELECT * FROM Ventas";
        $resultado = $conexion->query($sql);
        if ($resultado) {
            $ventas = $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Mostrar Detalle de Ventas
    if (isset($_POST['mostrarDetalleVentas'])) {
        $sql = "SELECT * FROM DetalleVentas";
        $resultado = $conexion->query($sql);
        if ($resultado) {
            $detalleVentas = $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Eliminar Venta
    if (isset($_POST['eliminarVenta'])) {
        $ventaID = $_POST['ventaID'];

        try {
            // Eliminar primero los detalles relacionados
            $conexion->begin_transaction();

            $stmt1 = $conexion->prepare("DELETE FROM DetalleVentas WHERE id_venta = ?");
            $stmt1->bind_param("i", $ventaID);
            $stmt1->execute();
            $stmt1->close();

            // Luego eliminar la venta principal
            $stmt2 = $conexion->prepare("DELETE FROM Ventas WHERE id_venta = ?");
            $stmt2->bind_param("i", $ventaID);
            $stmt2->execute();
            $stmt2->close();

            $conexion->commit();
            $mensaje = "Venta eliminada correctamente.";
        } catch (Exception $e) {
            $conexion->rollback();
            $mensaje = "Error al eliminar la venta: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Punto de Venta</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 2rem;
        }

        h2 {
            font-size: 1.8rem;
            color: #333;
            margin-top: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        form {
            margin: 20px 0;
        }

        input[type="number"] {
            padding: 10px;
            font-size: 1rem;
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }

        button {
            padding: 10px 20px;
            font-size: 1rem;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: rgb(49, 114, 52);
        }

        img {
            max-width: 50px;
            height: auto;
        }

        .eliminarVenta {
            background-color:rgb(188, 0, 0);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .eliminarVenta:hover {
            background-color:rgb(135, 22, 22);
        }

        .boton {
            background-color: #45a049;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .mensaje {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

<header>
    Tienda las pelonas
</header>

<div class="container">
    <!-- Botón Volver al Inicio -->
    <a href="index.php">
        <button class="boton">Volver al Inicio</button>
    </a>

    <h2>Ventas</h2>
    <form method="POST">
        <button type="submit" name="mostrarVentas">Mostrar Ventas</button>
    </form>

    <?php if (!empty($ventas)) { ?>
        <h3>Resultados de Ventas:</h3>
        <table>
            <tr>
                <th>ID Venta</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
            <?php foreach ($ventas as $venta) { ?>
                <tr>
                    <td><?= $venta['id_venta'] ?></td>
                    <td><?= $venta['Fecha'] ?></td>
                    <td><?= $venta['Total'] ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <h2>Detalle de Ventas</h2>
    <form method="POST">
        <button type="submit" name="mostrarDetalleVentas">Mostrar Detalle de Ventas</button>
    </form>

    <?php if (!empty($detalleVentas)) { ?>
        <h3>Resultados del Detalle de Ventas:</h3>
        <table>
            <tr>
                <th>DetalleID</th>
                <th>Foto</th>
                <th>ProductoID</th>
                <th>Id_venta</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($detalleVentas as $detalle) { ?>
                <tr>
                    <td><?= $detalle['DetalleID'] ?></td>
                    <td><img src="<?= $detalle['foto'] ?>" alt="Foto"></td>
                    <td><?= $detalle['ProductoID'] ?></td>
                    <td><?= $detalle['Id_venta'] ?></td>
                    <td><?= $detalle['Cantidad'] ?></td>
                    <td><?= $detalle['Subtotal'] ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <h2>Eliminar Venta</h2>
    <form method="POST">
        <label for="ventaID">ID de la Venta:</label>
        <input type="number" name="ventaID" required>
        <button class="eliminarVenta" type="submit" name="eliminarVenta">Eliminar Venta</button>
    </form>

    <?php if ($mensaje) { ?>
        <div class="mensaje <?= str_contains($mensaje, 'correctamente') ? 'success' : '' ?>">
            <p><?= $mensaje ?></p>
        </div>
    <?php } ?>
</div>

</body>
</html>
