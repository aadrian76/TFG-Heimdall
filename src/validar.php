<?php
include 'conexion.php'; // Tu archivo de conexión PDO o mysqli

if (isset($_POST['uid'])) {
    $uid = $_POST['uid'];

    // Consulta para ver si la tarjeta está activa y a quién pertenece
    $stmt = $conn->prepare("SELECT u.nombre, t.estado FROM tarjetas t 
                            JOIN usuarios u ON t.id_usuario = u.id_usuario 
                            WHERE t.uid_rfid = ?");
    $stmt->execute([$uid]);
    $resultado = $stmt->fetch();

    if ($resultado && $resultado['estado'] == 'activa') {
        // Registramos el acceso exitoso
        $log = $conn->prepare("INSERT INTO accesos (uid_rfid, acceso_concedido) VALUES (?, 1)");
        $log->execute([$uid]);
        
        echo "ALLOWED:" . $resultado['nombre']; // Respuesta para el ESP32
    } else {
        // Registro de intento fallido
        $log = $conn->prepare("INSERT INTO accesos (uid_rfid, acceso_concedido) VALUES (?, 0)");
        $log->execute([$uid]);
        
        echo "DENIED";
    }
}
?>