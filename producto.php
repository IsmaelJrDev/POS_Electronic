<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: secion.php");
    exit;
}

$conexion = new mysqli("localhost", "root", "root", "SistemaPOS");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$sql = "SELECT ProductoID, Nombre, Precio, Stock, foto FROM productos";
$result = $conexion->query($sql);

if (!$result) {
    die("Error al ejecutar la consulta: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Componentes de Computadoras</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #212529;
        }

        h1 {
            text-align: center;
            margin: 20px 0;
            font-size: 2.5rem;
            color: #333;
        }

        .contenedor {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .barra-superior {
            background-color: #4CAF50;
            padding: 15px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }

        .barra-superior a {
            text-decoration: none;
        }

        .boton {
            background-color: #fff;
            color: #4CAF50;
            border: 2px solid #4CAF50;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .boton:hover {
            background-color: #388E3C;
            color: #fff;
        }

        .productos-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .producto {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .producto:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .producto img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .producto h3 {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 10px;
        }

        .producto p {
            color: #555;
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .agregar-carrito {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .agregar-carrito:hover {
            background-color: #388E3C;
        }
    </style>
</head>
<body>
    <div class="barra-superior">
        <a href="inicio.php">
            <button class="boton">Volver al Inicio</button>
        </a>
        <a href="carrito.php">
            <button class="boton">Ir al Carrito</button>
        </a>
    </div>

    <div class="contenedor">
        <h1>Componentes Disponibles</h1>
        <div class="productos-container">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='producto'>";
                    echo "<img src='" . htmlspecialchars($row['foto']) . "' alt='" . htmlspecialchars($row['Nombre']) . "'>";
                    echo "<h3>" . htmlspecialchars($row['Nombre']) . "</h3>";
                    echo "<p>Precio: $" . htmlspecialchars($row['Precio']) . "</p>";
                    echo "<p>Stock disponible: " . htmlspecialchars($row['Stock']) . "</p>";
                    echo "<button class='agregar-carrito' data-id='" . htmlspecialchars($row['ProductoID']) . "'>Agregar al Carrito</button>";
                    echo "</div>";
                }
            } else {
                echo "<p>No se encontraron componentes.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php $conexion->close(); ?>