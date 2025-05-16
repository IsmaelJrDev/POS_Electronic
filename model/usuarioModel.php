<?php
require_once '../config/database.php';

class UsuarioModel {
    public static function verificarUsuario($username, $password) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = :user");
        $stmt->bindParam(':user', $username);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['password'] === $password) {
            return ['success' => true, 'rol' => $usuario['rol']];
        } else {
            return ['success' => false];
        }
    }
}
?>
