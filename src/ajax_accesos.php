<?php
include 'conexion.php';

try {
    $sql = "SELECT u.nombre, u.apellido, u.cargo, u.ruta_foto, a.fecha_hora 
            FROM accesos a
            JOIN tarjetas t ON a.uid_rfid = t.uid_rfid
            JOIN usuarios u ON t.id_usuario = u.id_usuario
            WHERE a.acceso_concedido = 1
            ORDER BY a.fecha_hora DESC LIMIT 1";

    $stmt = $conexion->query($sql);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($datos); // Enviamos solo los datos puros
} catch (Exception $e) {
    echo json_encode(['error' => 'db_error']);
}