<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "root", "SistemaPOS");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Función para agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productoId']) && isset($_POST['cantidad'])) {
    $productoId = intval($_POST['productoId']);
    $cantidad = intval($_POST['cantidad']);

    if ($cantidad <= 0) {
        echo "La cantidad debe ser un número mayor a 0.";
        exit;
    }

    // Verificar si hay suficiente stock
    $stmt = $conexion->prepare("CALL verificarStock(?)");
    $stmt->bind_param("i", $productoId);
    $stmt->execute();
    
    // Recuperar el resultado del procedimiento almacenado
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stockDisponible = $row['stockDisponible'];
    
    $stmt->close();
    
    // Verificar si la cantidad solicitada excede el stock disponible
    if ($cantidad > $stockDisponible) {
        echo "No hay suficiente stock disponible. Stock actual: $stockDisponible.";
        exit;
    }


    // Agregar al carrito
    $stmt = $conexion->prepare("CALL agregarAlCarrito(?, ?)");
    $stmt->bind_param("ii", $productoId, $cantidad);
    $stmt->execute();
    $stmt->close();

    echo "Producto agregado al carrito.";
    exit;
}

// Llamar al procedimiento almacenado para obtener los productos
$sql = "CALL obtenerProductos()";
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
    <title>Sistema POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos generales */
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

        /* Barra superior */
        .barra-superior {
            background-color: #28a745;
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
            color: #28a745;
            border: 2px solid #28a745;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .boton:hover {
            background-color:rgb(24, 80, 37);
            color: #fff;
        }

        /* Productos */
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
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .agregar-carrito:hover {
            background-color:rgb(23, 89, 37);
        }

        /* Estilo responsive */
        @media (max-width: 768px) {
            .productos-container {
                grid-template-columns: 1fr;
            }

            .barra-superior {
                flex-direction: column;
                gap: 10px;
            }

            .boton {
                width: 90%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Barra superior -->
    <div class="barra-superior">
        <a href="index.php">
            <button class="boton">Volver al Inicio</button>
        </a>
        <a href="carrito.php">
            <button class="boton">Ir al Carrito</button>
        </a>
    </div>

    <div class="contenedor">
        <h1>Productos Disponibles</h1>
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
                echo "<p>No se encontraron productos.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.agregar-carrito').forEach(button => {
            button.addEventListener('click', function() {
                var productoId = this.getAttribute('data-id');
                var cantidad = prompt("Ingresa la cantidad que deseas comprar:");
                if (!cantidad || isNaN(cantidad) || cantidad <= 0) {
                    alert("Por favor, ingresa una cantidad válida (mayor a 0).");
                    return;
                }
                var xhttp = new XMLHttpRequest();
                xhttp.open("POST", "", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("productoId=" + productoId + "&cantidad=" + cantidad);
                xhttp.onload = function() {
                    alert(xhttp.responseText);
                };
            });
        });
    </script>
</body>
</html>

<?php $conexion->close(); ?>
