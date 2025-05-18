-- Crea la base de datos
CREATE DATABASE IF NOT EXISTS posElectronic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE posElectronic;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    rol ENUM('admin', 'empleado') NOT NULL
);

-- Usuarios de ejemplo
INSERT INTO usuarios (username, password, rol) VALUES
('admin', 'admin123', 'admin'),
('juan', 'juan123', 'empleado');