USE Heimdall;

-- 1. Insertar Usuarios
INSERT INTO usuarios (nombre, apellido, documento, cargo, ruta_foto) VALUES
('Brais', 'García', '12345678A', 'Director de Seguridad', 'brais_g.jpg'),
('Lucía', 'Fernández', '87654321B', 'Analista de Sistemas', 'lucia_f.jpg'),
('Marcos', 'López', '45678912C', 'Mantenimiento', 'marcos_l.jpg'),
('Elena', 'Sanz', '11223344D', 'Recursos Humanos', 'default.png');

-- 2. Insertar Tarjetas
-- Nota: Los UID suelen ser valores hexadecimales en sistemas RFID
INSERT INTO tarjetas (uid_rfid, id_usuario, estado) VALUES
('4A:5B:6C:7D', 1, 'activa'),
('12:34:56:78', 2, 'activa'),
('99:AA:BB:CC', 3, 'perdida'), -- Marcos perdió su tarjeta
('EE:FF:00:11', 4, 'activa'),
('A1:B2:C3:D4', 3, 'activa'); -- Nueva tarjeta asignada a Marcos

-- 3. Insertar Accesos (Simulando entradas y salidas)
INSERT INTO accesos (uid_rfid, fecha_hora, tipo_acceso, acceso_concedido) VALUES
('4A:5B:6C:7D', '2026-04-07 08:00:00', 'entrada', TRUE),
('12:34:56:78', '2026-04-07 08:15:00', 'entrada', TRUE),
('99:AA:BB:CC', '2026-04-07 09:00:00', 'entrada', FALSE), -- Intento fallido con tarjeta perdida
('4A:5B:6C:7D', '2026-04-07 14:00:00', 'salida', TRUE),
('A1:B2:C3:D4', '2026-04-07 14:30:00', 'entrada', TRUE);

-- 4. Insertar Administradores
-- Las contraseñas son ejemplos; en producción usa password_hash() de PHP
INSERT INTO administradores (usuario_login, password_hash, ultimo_login) VALUES
('admin_root', '$2y$10$snBxlsqh/Apsag56i7Yt3eo9HGCX2qkXfcr/c0yU71zLtSdZ6mJEG', NOW()), -- Hash de 'password123'
('operador_01', '$2y$10$vZK9EH3bdZFTVF008qopWuqfWSnRy5YykldW/bDa0gaMUObZy0Fam', NULL); -- Hash de '1qAz2wsx'