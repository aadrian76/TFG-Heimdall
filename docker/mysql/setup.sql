-- 1. Creación de la base de datos (si no existe)
CREATE DATABASE IF NOT EXISTS Heimdall;
USE Heimdall;

-- 2. Limpieza de tablas previas (opcional, útil en desarrollo)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS accesos;
DROP TABLE IF EXISTS tarjetas;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- 3. Tabla de Usuarios (Maestra)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    documento CHAR(9) UNIQUE NOT NULL, -- DNI (CHAR porque siempre tiene 9 caracteres)
    cargo VARCHAR(50),
    ruta_foto VARCHAR(255) DEFAULT 'default.png',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabla de Tarjetas (Relacional)
CREATE TABLE tarjetas (
    uid_rfid VARCHAR(50) PRIMARY KEY,
    id_usuario INT,
    estado ENUM('activa', 'inactiva', 'perdida') DEFAULT 'activa',
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_tarjeta FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
);

-- 5. Tabla de Accesos (Transaccional)
CREATE TABLE accesos (
    id_acceso INT AUTO_INCREMENT PRIMARY KEY,
    uid_rfid VARCHAR(50) NOT NULL,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_acceso ENUM('entrada', 'salida') DEFAULT 'entrada',
    acceso_concedido BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_tarjeta_acceso FOREIGN KEY (uid_rfid) 
        REFERENCES tarjetas(uid_rfid) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 6. Insertar datos de prueba (Seeders)
-- Esto te servirá para probar tu pantalla PHP de inmediato
INSERT INTO usuarios (nombre, apellido, documento, cargo, ruta_foto) VALUES 
('Elon', 'Musk', '12345678', 'Director', 'elon.jpg'),
('Ada', 'Lovelace', '87654321', 'Ingeniera', 'ada.jpg');

INSERT INTO tarjetas (uid_rfid, id_usuario, estado) VALUES 
('A1 B2 C3 D4', 1, 'activa'),
('E5 F6 G7 H8', 2, 'activa');