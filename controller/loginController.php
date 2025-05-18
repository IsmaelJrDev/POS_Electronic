<?php
// Controlador para procesar el inicio de sesión.

require_once '../model/usuarioModel.php';

// Obtiene los datos enviados por POST en formato JSON
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$password = $data['password'];

// Verifica las credenciales y responde con JSON
$resultado = UsuarioModel::verificarUsuario($username, $password);
echo json_encode($resultado);
?>