<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "root", "SistemaPOS");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = null;
$productos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CONSULTAR PRODUCTO
    if (isset($_POST['consultar'])) {
        $criterio = $_POST['criterio'];
        $param = "%$criterio%";

        if (is_numeric($criterio)) {
            $stmt = $conn->prepare("SELECT * FROM Productos WHERE ProductoID = ? OR Nombre LIKE ?");
            $stmt->bind_param("is", $criterio, $param);
        } else {
            $stmt = $conn->prepare("SELECT * FROM Productos WHERE Nombre LIKE ?");
            $stmt->bind_param("s", $param);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $productos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (count($productos) == 0) {
            echo "<script>alert('El ID o nombre del producto no existe.');</script>";
        }
    }

    // REGISTRAR PRODUCTO
    if (isset($_POST['registrar'])) {
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];

        if ($precio < 0 || $stock < 0) {
            $mensaje = "No puedes ingresar valores en negativo";
        } else {
            if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filePath = $uploadDir . basename($_FILES['foto']['name']);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $filePath)) {
                    $foto = $filePath;

                    $stmt = $conn->prepare("INSERT INTO Productos (Nombre, Precio, Stock, foto) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sdss", $nombre, $precio, $stock, $foto);
                    if ($stmt->execute()) {
                        $mensaje = "Producto registrado correctamente.";
                    } else {
                        $mensaje = "Error al registrar producto.";
                    }
                    $stmt->close();
                } else {
                    $mensaje = "Error al subir la imagen.";
                }
            }
        }

        if (isset($mensaje)) echo "<script>alert('$mensaje');</script>";
    }

    // ELIMINAR PRODUCTO
    if (isset($_POST['eliminar'])) {
        $productoID = $_POST['productoID'];

        $stmt = $conn->prepare("DELETE FROM Productos WHERE ProductoID = ?");
        $stmt->bind_param("i", $productoID);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo "<script>alert('Producto eliminado exitosamente.');</script>";
        } else {
            echo "<script>alert('El ID del producto no existe.');</script>";
        }
        $stmt->close();
    }

    // MODIFICAR PRODUCTO
    if (isset($_POST['modificar'])) {
        $productoID = $_POST['productoID'];
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];

        if ($precio < 0 || $stock < 0) {
            echo "<script>alert('Valor negativo o ID inexistente.');</script>";
        } else {
            $foto = null;
            if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filePath = $uploadDir . basename($_FILES['foto']['name']);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $filePath)) {
                    $foto = $filePath;
                }
            }

            if ($foto) {
                $stmt = $conn->prepare("UPDATE Productos SET Nombre=?, Precio=?, Stock=?, foto=? WHERE ProductoID=?");
                $stmt->bind_param("sdssi", $nombre, $precio, $stock, $foto, $productoID);
            } else {
                $stmt = $conn->prepare("UPDATE Productos SET Nombre=?, Precio=?, Stock=? WHERE ProductoID=?");
                $stmt->bind_param("dsdi", $nombre, $precio, $stock, $productoID);
            }

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo "<script>alert('Producto modificado correctamente.');</script>";
            } else {
                echo "<script>alert('No se pudo modificar el producto.');</script>";
            }

            $stmt->close();
        }
    }

    // AGREGAR STOCK
    if (isset($_POST['agregarStock'])) {
        $productoID = $_POST['productoID'];
        $cantidad = $_POST['cantidad'];

        if ($cantidad < 0) {
            echo "<script>alert('Cantidad negativa.');</script>";
        } else {
            $stmt = $conn->prepare("UPDATE Productos SET Stock = Stock + ? WHERE ProductoID = ?");
            $stmt->bind_param("ii", $cantidad, $productoID);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script>alert('Stock actualizado correctamente.');</script>";
            } else {
                echo "<script>alert('El ID del producto no existe.');</script>";
            }

            $stmt->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Punto de Venta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        h1 {
            margin: 0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            margin: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        table th {
            background-color: #f2f2f2;
        }
        input[type="text"], input[type="number"], input[type="file"], input[type="date"], button {
            padding: 8px;
            margin: 5px 0;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<header>
    <h1>Tienda las pelonas</h1>
</header>

<div class="form-container">
    <a href="index.php" style="text-decoration: none;">
        <button style="background-color: #4CAF50; color: white;">Volver al Menú Principal</button>
    </a>
    <h2>Consultar Producto</h2>
    <form method="POST">
        <input type="text" name="criterio" placeholder="Buscar por ID o nombre" >
        <button type="submit" name="consultar">Consultar</button>
    </form>
    <?php if (!empty($productos)) { ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Foto</th>
            </tr>
            <?php foreach ($productos as $producto) { ?>
                <tr>
                    <td><?= $producto['ProductoID'] ?></td>
                    <td><?= $producto['Nombre'] ?></td>
                    <td><?= $producto['Precio'] ?></td>
                    <td><?= $producto['Stock'] ?></td>
                    <td><img src="<?= $producto['foto'] ?>" alt="Foto" width="50"></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>

<div class="form-container">
    <h2>Registrar Producto</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="nombre" placeholder="Nombre" required><br>
        <input type="number" step="0.01" name="precio" placeholder="Precio" required><br>
        <input type="number" name="stock" placeholder="Stock" required><br>
        <input type="file" name="foto" required><br>
        <button type="submit" name="registrar">Registrar Producto</button>
    </form>
</div>

<div class="form-container">
    <h2>Eliminar Producto</h2>
    <form method="POST">
        <input type="number" name="productoID" placeholder="ID del Producto" required><br>
        <button type="submit" name="eliminar">Eliminar Producto</button>
    </form>
</div>

<div class="form-container">
    <h2>Modificar Producto</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="number" name="productoID" placeholder="ID del Producto" required><br>
        <input type="text" name="nombre" placeholder="Nuevo Nombre"><br>
        <input type="number" step="0.01" name="precio" placeholder="Nuevo Precio"><br>
        <input type="number" name="stock" placeholder="Nuevo Stock"><br>
        <input type="file" name="foto" placeholder="Nueva Foto"><br>
        <button type="submit" name="modificar">Modificar Producto</button>
    </form>
</div>

<div class="form-container">
    <h2>Agregar Stock a Producto</h2>
    <form method="POST">
        <input type="number" name="productoID" placeholder="ID del Producto" required><br>
        <input type="number" name="cantidad" placeholder="Cantidad a Agregar" required><br>
        <button type="submit" name="agregarStock">Agregar Stock</button>
    </form>
</div>

</body>
</html>
