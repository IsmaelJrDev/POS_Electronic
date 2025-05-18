<?php
require_once '../model/usuarioModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$password = $data['password'];

$resultado = UsuarioModel::verificarUsuario($username, $password);
echo json_encode($resultado);
?>
