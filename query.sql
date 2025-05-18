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

-- Productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255),
    categoria VARCHAR(50), -- Ej: "Procesador", "RAM", "Placa Madre"
    marca VARCHAR(50),      -- Ej: "Intel", "AMD", "Corsair"
    modelo VARCHAR(50),     -- Ej: "Ryzen 5 5600X"
    fecha_ingreso DATE DEFAULT CURRENT_DATE,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- Insertar datos para productos
INSERT INTO productos (nombre, descripcion, precio, stock, imagen, categoria, marca, modelo)
VALUES
('Procesador Ryzen 5 5600X', '6 núcleos, 12 hilos, excelente rendimiento gaming.', 2899.99, 10, 'ryzen5600x.jpg', 'Procesador', 'AMD', '5600X'),

('Memoria RAM Corsair Vengeance 16GB', 'DDR4, 3200MHz, ideal para gaming y multitarea.', 1249.50, 15, 'corsair16gb.jpg', 'RAM', 'Corsair', 'Vengeance LPX'),

('Tarjeta Madre ASUS B550M', 'Soporta procesadores Ryzen, PCIe 4.0.', 1999.00, 5, 'asusb550m.jpg', 'Placa Madre', 'ASUS', 'B550M-A'),

('SSD Kingston NV2 1TB', 'M.2 NVMe PCIe Gen 4, velocidad de lectura hasta 3500MB/s.', 1299.99, 8, 'kingston1tb.jpg', 'Almacenamiento', 'Kingston', 'NV2'),

('Fuente de Poder EVGA 600W', '80+ White, excelente para PCs de gama media.', 899.00, 12, 'evga600w.jpg', 'Fuente de Poder', 'EVGA', '600W White');

