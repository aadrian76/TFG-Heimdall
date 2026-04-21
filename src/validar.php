<?php
include 'conexion.php'; // Aquí se inicializa $conexion (PDO)

// El ESP32 debe enviar el UID por POST
if (isset($_POST['uid'])) {
    $uid = $_POST['uid'];

    try {
        // 1. Buscamos la tarjeta y el usuario asociado
        // Usamos los nombres de tus tablas: usuarios, tarjetas
        $stmt = $conexion->prepare("
            SELECT u.id_usuario, u.nombre, t.estado 
            FROM tarjetas t 
            INNER JOIN usuarios u ON t.id_usuario = u.id_usuario 
            WHERE t.uid_rfid = ?
        ");
        $stmt->execute([$uid]);
        $resultado = $stmt->fetch();

        // 2. Lógica de validación
        if ($resultado && $resultado['estado'] == 'activa') {
            
            // Registro de acceso exitoso en la tabla 'accesos'
            $log = $conexion->prepare("
                INSERT INTO accesos (uid_rfid, tipo_acceso, acceso_concedido) 
                VALUES (?, 'entrada', 1)
            ");
            $log->execute([$uid]);
            
            // Respuesta que leerá el ESP32
            echo "ALLOWED:" . $resultado['nombre']; 
            
        } else {
            // Si la tarjeta no existe o no está activa, registramos el intento fallido
            // Nota: Si el UID no existe en 'tarjetas', la FK de 'accesos' podría dar error 
            // dependiendo de tu configuración, pero aquí registramos el fallo.
            
            if($resultado) {
                $log = $conexion->prepare("INSERT INTO accesos (uid_rfid, tipo_acceso, acceso_concedido) VALUES (?, 'entrada', 0)");
                $log->execute([$uid]);
            }
            
            echo "DENIED";
        }

    } catch (PDOException $e) {
        // En producción, mejor loguear el error y no mostrarlo
        echo "ERROR_SERVER";
    }
} else {
    echo "NO_UID_RECEIVED";
}
?>