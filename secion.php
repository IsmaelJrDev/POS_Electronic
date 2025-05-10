<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "SistemaPOS");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['iniciar'])) {
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    $stmt = $conn->prepare("SELECT id_usuario, nombre, contraseña FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id_usuario, $nombre, $hash);
        $stmt->fetch();

        if (password_verify($contraseña, $hash)) {
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['correo'] = $correo;
            header("Location: inicio.php");
            exit;
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "El correo no está registrado.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .login-container button:hover {
            background: #0056b3;
        }
        .mensaje {
            color: red;
            margin-top: 10px;
            font-weight: bold;
        }
        .registro-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .registro-link a {
            color: #007bff;
            text-decoration: none;
        }
        .registro-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form method="POST">
            <input type="email" name="correo" placeholder="Correo" required><br>
            <input type="password" name="contraseña" placeholder="Contraseña" required><br>
            <button type="submit" name="iniciar">Iniciar Sesión</button>
        </form>

        <?php if (!empty($mensaje)): ?>
            <p class="mensaje"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <div class="registro-link">
            ¿No tienes una cuenta? <a href="registro.php">Regístrate</a>
        </div>
    </div>
</body>
</html>
