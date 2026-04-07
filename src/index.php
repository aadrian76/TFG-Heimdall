<?php
session_start();
include 'conexion.php';

// Si NO tiene sesión iniciada (está vacío el admin_id), lo mandamos al login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/**
 * Consulta para obtener el último acceso concedido
 * Unimos 'accesos' con 'tarjetas' y 'usuarios' para traer nombre, cargo y foto
 */
try {
    $sql = "SELECT u.nombre, u.apellido, u.cargo, u.ruta_foto, a.fecha_hora, t.uid_rfid
            FROM accesos a
            JOIN tarjetas t ON a.uid_rfid = t.uid_rfid
            JOIN usuarios u ON t.id_usuario = u.id_usuario
            WHERE a.acceso_concedido = 1
            ORDER BY a.fecha_hora DESC 
            LIMIT 1";

    $stmt = $conexion->query($sql);
    $ultimo = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Error al conectar con la base de datos";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heimdall | Panel de Control</title>
    <style>
        :root { --primary: #00d4ff; --bg: #121212; --card: #1e1e26; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); color: white; margin: 0; display: flex; flex-direction: column; align-items: center; }
        
        header { width: 100%; padding: 20px; background: #000; text-align: center; border-bottom: 2px solid var(--primary); box-shadow: 0 4px 10px rgba(0,212,255,0.2); }
        h1 { margin: 0; letter-spacing: 3px; font-weight: 300; color: var(--primary); }

        .container { margin-top: 50px; text-align: center; }
        
        .access-card {
            background: var(--card);
            border-radius: 20px;
            padding: 40px;
            width: 350px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border: 1px solid #333;
            transition: all 0.5s ease;
        }

        .photo-container {
            width: 180px;
            height: 180px;
            margin: 0 auto 20px;
            border-radius: 50%;
            border: 4px solid var(--primary);
            overflow: hidden;
            background: #252525;
        }

        .photo-container img { width: 100%; height: 100%; object-fit: cover; }

        .name { font-size: 1.8rem; margin: 10px 0; font-weight: bold; }
        .cargo { color: var(--primary); font-size: 1.1rem; text-transform: uppercase; margin-bottom: 20px; display: block; }
        
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-top: 1px solid #333; font-size: 0.9rem; color: #aaa; }
        .status { color: #00ff88; font-weight: bold; }

        .no-data { padding: 40px; color: #666; font-style: italic; }
    </style>
</head>
<body>

    <header>
        <h1>HEIMDALL SYSTEM</h1>
        <small>Control de Acceso Biométrico / RFID</small>
    </header>

    <div class="container" id="content-area">
        <?php if ($ultimo): ?>
            <div class="access-card">
                <div class="photo-container">
                    <img src="fotos/<?php echo htmlspecialchars($ultimo['ruta_foto']); ?>" alt="Foto Usuario">
                </div>
                
                <span class="status">● ACCESO CONCEDIDO</span>
                <div class="name"><?php echo $ultimo['nombre'] . " " . $ultimo['apellido']; ?></div>
                <span class="cargo"><?php echo $ultimo['cargo']; ?></span>

                <div class="info-row">
                    <span>UID Tarjeta:</span>
                    <span><?php echo $ultimo['uid_rfid']; ?></span>
                </div>
                <div class="info-row">
                    <span>Hora de Entrada:</span>
                    <span><?php echo date("H:i:s", strtotime($ultimo['fecha_hora'])); ?></span>
                </div>
                <div class="info-row">
                    <span>Fecha:</span>
                    <span><?php echo date("d/m/Y", strtotime($ultimo['fecha_hora'])); ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="access-card no-data">
                <p>Esperando lectura de tarjeta...</p>
                <div class="spinner"></div>
            </div>
        <?php endif; ?>
    </div>

 <script>
    // Variable para saber cuál fue el último acceso que mostramos
    let ultimaFechaRegistrada = "";

    function actualizarDashboard() {
        // Consultamos al archivo ligero que solo devuelve datos JSON
        fetch('ajax_accesos.php')
            .then(response => response.json())
            .then(data => {
                // Si hay datos y la fecha es distinta a la que tenemos, actualizamos
                if (data && data.fecha_hora !== ultimaFechaRegistrada) {
                    ultimaFechaRegistrada = data.fecha_hora;

                    // 1. Buscamos el contenedor principal
                    const contentArea = document.getElementById('content-area');

                    // 2. Inyectamos el nuevo HTML con los datos recibidos
                    // Usamos los nombres de tu tabla: nombre, apellido, cargo, ruta_foto
                    contentArea.innerHTML = `
                        <div class="access-card">
                            <div class="photo-container">
                                <img src="fotos/${data.ruta_foto}" alt="Foto Usuario">
                            </div>
                            
                            <span class="status">● ACCESO CONCEDIDO</span>
                            <div class="name">${data.nombre} ${data.apellido}</div>
                            <span class="cargo">${data.cargo}</span>

                            <div class="info-row">
                                <span>Hora de Entrada:</span>
                                <span>${data.fecha_hora.split(' ')[1]}</span>
                            </div>
                            <div class="info-row">
                                <span>Fecha:</span>
                                <span>${data.fecha_hora.split(' ')[0]}</span>
                            </div>
                        </div>
                    `;
                    
                    console.log("Nuevo acceso detectado: " + data.nombre);
                }
            })
            .catch(error => console.error('Error al obtener datos:', error));
    }

    // Ejecutamos la función cada 1.5 segundos para que sea casi instantáneo
    setInterval(actualizarDashboard, 1500);
</script>

</body>
</html>