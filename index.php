<?php
session_start();

$conexion = new mysqli("localhost", "root", "root", "SistemaPOS");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Componentes de Computadoras</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            color: #333;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        header h1 {
            margin: 0;
        }

        nav {
            background-color: #333;
            padding: 10px;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .container {
            max-width: 1100px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 30px;
        }

        .links-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .link-card {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            margin: 15px;
            width: 250px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .link-card:hover {
            transform: translateY(-5px);
            background-color: #45a049;
        }

        .link-card a {
            color: white;
            font-size: 18px;
            text-decoration: none;
        }

        .link-card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <header>
        <h1>Tienda de Componentes de Computadoras</h1>
    </header>

    <div class="container">
        <div class="links-container">
            <div class="link-card">
                <a href="cambios.php">Gestión de Componentes</a>
                <p>Administra los componentes: creación, eliminación y modificación.</p>
            </div>
            <div class="link-card">
                <a href="producto.php">Explorar Componentes</a>
                <p>Agrega componentes al carrito de compras.</p>
            </div>
            <div class="link-card">
                <a href="Venta.php">Historial de Ventas</a>
                <p>Consulta las ventas realizadas.</p>
            </div>
        </div>
    </div>

</body>
</html>

<?php
$conexion->close();
?>