<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "root", "SistemaPOS");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}


$mensaje = null;
$productos = [];
$ventas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Consultar Productos
    if (isset($_POST['consultar'])) {
        $criterio = $_POST['criterio'];
        $stmt = $conn->prepare("CALL ConsultarProductos(?)");
        $stmt->bind_param("s", $criterio);
        $stmt->execute();
        $result = $stmt->get_result();
        $productos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();    

        // Verificar si se encontraron productos
        if (count($productos) == 0) {
            echo "<script>alert('El ID del producto ingresado no existe.');</script>";
        }
    }


    // Registrar Producto
    if (isset($_POST['registrar'])) {
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
    
        // Validación para asegurarse de que los valores no sean negativos
        if ($precio < 0) {
            $mensaje = "No puedes ingresar valores en negativo";
        } elseif ($stock < 0) {
            $mensaje = "No puedes ingresar valores en negativo";
        } else {
            // Subir la imagen
            if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['foto']['tmp_name'];
                $fileName = $_FILES['foto']['name'];
                $fileSize = $_FILES['foto']['size'];
                $fileType = $_FILES['foto']['type'];
    
                // Aseguramos que la carpeta uploads exista
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
    
                $filePath = $uploadDir . basename($fileName);
    
                // Mover el archivo de la carpeta temporal a la carpeta uploads
                if (move_uploaded_file($fileTmpPath, $filePath)) {
                    $foto = $filePath;
                } else {
                    $mensaje = "Error al subir la imagen.";
                }
            }
    
            if (isset($foto)) {
                try {
                    $stmt = $conn->prepare("CALL RegistrarProducto(?, ?, ?, ?)");
                    $stmt->bind_param("sdss", $nombre, $precio, $stock, $foto);
                    $stmt->execute();
    
                    $result = $stmt->get_result();
                    $mensaje = $result->fetch_assoc()['Mensaje'];
                    $stmt->close();
                } catch (Exception $e) {
                    $mensaje = $e->getMessage();
                }
            }
        }
    
        // Si hay mensaje, mostrarlo como un alert en JavaScript
        if (isset($mensaje)) {
            echo "<script>alert('$mensaje');</script>";
        }
    }
    
    // Eliminar Producto
    if (isset($_POST['eliminar'])) {
        $productoID = $_POST['productoID'];
    
        try {
            $stmt = $conn->prepare("CALL EliminarProducto(?)");
            $stmt->bind_param("i", $productoID);
            $stmt->execute();
    
            $result = $stmt->get_result();
            $productos = $result->fetch_all(MYSQLI_ASSOC);
            $mensaje = $result->fetch_assoc()['Mensaje'];
            $stmt->close();
        } catch (Exception $e) {
            $mensaje = $e->getMessage();
        }
    
        // Verificar si se encontraron productos
        if (count($productos) == 0) {
            echo "<script>alert('El ID del producto ingresado no existe.');</script>";
        }
    }
    
        // Modificar Producto
    if (isset($_POST['modificar'])) {
        $productoID = $_POST['productoID'];
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
    
        // Verificar si el precio o el stock son negativos
        if ($precio < 0) {
            echo "<script>alert('Ingreso un valor negativo o un id de un producto no existente');</script>";
        } elseif ($stock < 0) {
            echo "<script>alert('Ingreso un valor negativo o un id de un producto no existente');</script>";
        } else {
            // Verificamos si se ha subido una nueva foto
            if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['foto']['tmp_name'];
                $fileName = $_FILES['foto']['name'];
                $fileSize = $_FILES['foto']['size'];
                $fileType = $_FILES['foto']['type'];
    
                // Aseguramos que la carpeta uploads exista
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
    
                $filePath = $uploadDir . basename($fileName);
    
                // Mover el archivo de la carpeta temporal a la carpeta uploads
                if (move_uploaded_file($fileTmpPath, $filePath)) {
                    $foto = $filePath; // Actualizamos la foto con la nueva ruta
                } else {
                    $mensaje = "Error al subir la nueva imagen.";
                }
            } else {
                // Si no se subió una nueva foto, podemos dejar el valor de la foto como nulo o con la foto existente
                $foto = null;
            }
    
            // Procedemos a modificar el producto, incluyendo la foto si la cargó
            try {
                // Si hay foto nueva, la actualizamos
                if ($foto) {
                    $stmt = $conn->prepare("CALL ModificarProducto(?, ?, ?, ?, ?)");
                    $stmt->bind_param("isdss", $productoID, $nombre, $precio, $stock, $foto);
                } else {
                    // Si no hay foto nueva, no la actualizamos
                    $stmt = $conn->prepare("CALL ModificarProducto(?, ?, ?, ?, NULL)");
                    $stmt->bind_param("isd", $productoID, $nombre, $precio, $stock);
                }
    
                $stmt->execute();
                $result = $stmt->get_result();
                $mensaje = $result->fetch_assoc()['Mensaje'];
                $stmt->close();
            } catch (Exception $e) {
                $mensaje = $e->getMessage();
            }
        }
    }
    if (isset($_POST['agregarStock'])) {
        $productoID = $_POST['productoID'];
        $cantidad = $_POST['cantidad'];
    
        // Verificar si la cantidad es negativa
        if ($cantidad < 0) {
            echo "<script>alert('Ingresó un valor negativo.');</script>";
        } else {
            try {
                // Verificar si el producto existe
                $stmt = $conn->prepare("SELECT COUNT(*) AS Existe FROM Productos WHERE ProductoID = ?");
                $stmt->bind_param("i", $productoID);
                $stmt->execute();
                $result = $stmt->get_result();
                $existe = $result->fetch_assoc()['Existe'];
                $stmt->close();
    
                if ($existe == 0) {
                    // Si el producto no existe, mostrar alerta
                    echo "<script>alert('El ID del producto no existe.');</script>";
                } else {
                    // Si el producto existe, llamar al procedimiento para actualizar el stock
                    $stmt = $conn->prepare("CALL ActualizarStock(?, ?)");
                    $stmt->bind_param("ii", $productoID, $cantidad);
                    $stmt->execute();
                    $stmt->close();
    
        
                }
            } catch (Exception $e) {
                echo "<script>alert('Ocurrió un error al actualizar el stock.');</script>";
            }
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
