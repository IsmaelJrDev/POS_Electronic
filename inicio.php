<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: secion.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .inicio-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .inicio-container h2 {
            margin-bottom: 20px;
        }
        .inicio-container a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
        }
        .inicio-container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="inicio-container">
        <h2>Bienvenida, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h2>
        <a href="producto.php">Ir a productos</a>
    </div>
</body>
</html>
