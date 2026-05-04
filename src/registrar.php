<?php
include 'conexion.php'; // Usa la conexión configurada para Docker

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $documento = $_POST['documento'];
    $cargo = $_POST['cargo'];
    $uid = $_POST['uid']; // El UID capturado por el RFID

    try {
        $conexion->beginTransaction();

        // 1. Insertar en la tabla usuarios
        $sqlUsuario = "INSERT INTO usuarios (nombre, apellido, documento, cargo) VALUES (?, ?, ?, ?)";
        $stmtU = $conexion->prepare($sqlUsuario);
        $stmtU->execute([$nombre, $apellido, $documento, $cargo]);
        
        // Obtenemos el ID del usuario recién creado
        $id_usuario = $conexion->lastInsertId();

        // 2. Insertar en la tabla tarjetas[cite: 3]
        $sqlTarjeta = "INSERT INTO tarjetas (uid_rfid, id_usuario, estado) VALUES (?, ?, 'activa')";
        $stmtT = $conexion->prepare($sqlTarjeta);
        $stmtT->execute([$uid, $id_usuario]);

        $conexion->commit();
        $mensaje = "<div style='color:green;'>Usuario y Tarjeta registrados con éxito.</div>";
    } catch (Exception $e) {
        $conexion->rollBack();
        $mensaje = "<div style='color:red;'>Error al registrar: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Personal - Heimdall</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f4f4f4; }
        .form-container { background: white; padding: 20px; border-radius: 8px; max-width: 400px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 8px; margin: 10px 0; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Alta de Nuevo Usuario</h2>
    <?php echo $mensaje; ?>
    
    <div class="form-container">
        <form method="POST">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>
            
            <label>Apellido:</label>
            <input type="text" name="apellido" required>
            
            <label>Documento (DNI):</label>
            <input type="text" name="documento" maxlength="9" required>
            
            <label>Cargo:</label>
            <input type="text" name="cargo">
            
            <label style="color: blue; font-weight: bold;">UID de Tarjeta (RFID):</label>
            <input type="text" name="uid" placeholder="Pasa la tarjeta por el lector..." required>
            
            <button type="submit">Guardar Usuario y Tarjeta</button>
        </form>
    </div>
</body>
</html>