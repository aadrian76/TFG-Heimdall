<?php
session_start();

// ── AUTENTICACIÓN DUAL ────────────────────────────────────────────
$token_recibido = $_SERVER['HTTP_X_ESP32_TOKEN'] ?? '';
$token_esperado = getenv('ESP32_TOKEN');
$es_esp32 = !empty($token_recibido);

if ($es_esp32) {
    // Petición del ESP32 → validar por token
    if (!hash_equals($token_esperado, $token_recibido)) {
        http_response_code(401);
        die("No autorizado");
    }
} else {
    // Petición desde navegador → validar por sesión
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
}
// ─────────────────────────────────────────────────────────────────

include 'conexion.php'; // Usa la conexión configurada para Docker[cite: 6]

$mensaje = "";
$archivo_uid = 'ultimo_uid.txt'; // Archivo temporal para comunicar el ESP32 y la Web

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // CASO 1: La petición viene del ESP32 (solo envía 'uid', no hay 'nombre')
    if (isset($_POST['uid']) && !isset($_POST['nombre'])) {
        $uid_recibido = trim($_POST['uid']);
        // Guardamos el UID en el archivo de texto temporal
        file_put_contents($archivo_uid, $uid_recibido);
        echo "UID_RECEIVED_BY_SERVER";
        exit; // Detenemos el script aquí para que no intente cargar el HTML ni insertar en la BD
    }

    // CASO 2: La petición viene del formulario Web (trae nombre, apellidos, etc.)[cite: 6]
    if (isset($_POST['nombre']) && isset($_POST['uid'])) {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $documento = $_POST['documento'];
        $cargo = $_POST['cargo'];
        $uid = $_POST['uid'];

        try {
            $conexion->beginTransaction();

            // 1. Insertar en la tabla usuarios[cite: 6]
            $sqlUsuario = "INSERT INTO usuarios (nombre, apellido, documento, cargo) VALUES (?, ?, ?, ?)";
            $stmtU = $conexion->prepare($sqlUsuario);
            $stmtU->execute([$nombre, $apellido, $documento, $cargo]);
            
            $id_usuario = $conexion->lastInsertId();

            // 2. Insertar en la tabla tarjetas[cite: 6]
            $sqlTarjeta = "INSERT INTO tarjetas (uid_rfid, id_usuario, estado) VALUES (?, ?, 'activa')";
            $stmtT = $conexion->prepare($sqlTarjeta);
            $stmtT->execute([$uid, $id_usuario]);

            $conexion->commit();
            $mensaje = "<div style='color:green; font-weight:bold;'>✓ Usuario y Tarjeta registrados con éxito.</div>";
            
            // Limpiamos el archivo temporal para no reusar el mismo UID por accidente
            file_put_contents($archivo_uid, "");
            
        } catch (Exception $e) {
            $conexion->rollBack();
            $mensaje = "<div style='color:red; font-weight:bold;'>✗ Error al registrar: " . $e->getMessage() . "</div>";
        }
    }
}

// Leer el último UID escaneado para pre-rellenar el formulario
$uid_actual = file_exists($archivo_uid) ? file_get_contents($archivo_uid) : "";
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
        .uid-box { background: #eef; border: 1px solid #ccf; padding: 10px; border-radius: 4px; }
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
            
            <div class="uid-box">
                <label style="color: blue; font-weight: bold;">UID de Tarjeta (RFID):</label>
                <!-- Aquí inyectamos el UID que el ESP32 dejó en el archivo temporal -->
                <input type="text" name="uid" value="<?php echo htmlspecialchars($uid_actual); ?>" placeholder="Pasa la tarjeta y recarga la página..." required readonly>
            </div>
            
            <button type="submit">Guardar Usuario y Tarjeta</button>
        </form>
    </div>
</body>
</html>