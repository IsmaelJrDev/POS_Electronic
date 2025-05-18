<?php
// Carga las variables de entorno desde el archivo .env
$dotenv = parse_ini_file(__DIR__ . '/../.env');

$host = $dotenv['DB_HOST'];
$db = $dotenv['DB_NAME'];
$user = $dotenv['DB_USER'];
$pass = $dotenv['DB_PASS'];

// Intenta establecer la conexión PDO con la base de datos
try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>