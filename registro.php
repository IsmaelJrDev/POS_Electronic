<?php
session_start(); // Inicia la sesión para acceder a las variables de sesión

$conn = new mysqli("localhost", "root", "root", "SistemaPOS");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);

    // Verificar si ya existe el correo
    $check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $mensaje = "El correo ya está registrado.";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contraseña) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $correo, $contraseña);
        if ($stmt->execute()) {
            $mensaje = "Usuario registrado exitosamente.";
        } else {
            $mensaje = "Error al registrar usuario.";
        }
        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .registro-container {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        .registro-container h2 {
            margin-bottom: 20px;
        }
        .registro-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .registro-container button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            border: none;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .registro-container button:hover {
            background: #218838;
        }
        .mensaje {
            margin-top: 10px;
            font-weight: bold;
            color: <?php echo ($mensaje === "Usuario registrado exitosamente.") ? 'green' : 'red'; ?>;
        }
        .volver-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .volver-link a {
            color: #007bff;
            text-decoration: none;
        }
        .volver-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="registro-container">
        <h2>Registrar Usuario</h2>
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre" required><br>
            <input type="email" name="correo" placeholder="Correo" required><br>
            <input type="password" name="contraseña" placeholder="Contraseña" required><br>
            <button type="submit" name="registrar">Registrarse</button>
        </form>

        <?php if (!empty($mensaje)): ?>
            <p class="mensaje"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <div class="volver-link">
            <a href="secion.php">Volver al inicio de sesión</a>
        </div>
    </div>
</body>
</html>
