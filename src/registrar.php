<?php
session_start();

$token_recibido = $_SERVER['HTTP_X_ESP32_TOKEN'] ?? '';
$token_esperado = getenv('ESP32_TOKEN');
$es_esp32       = !empty($token_recibido);

if ($es_esp32) {
    if (!hash_equals($token_esperado, $token_recibido)) {
        http_response_code(401);
        die("No autorizado");
    }
} else {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
}

include 'conexion.php';

$mensaje     = "";
$tipo_msg    = "";
$archivo_uid = '/var/data/ultimo_uid.txt';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CASO 1: ESP32 envía UID
    if (isset($_POST['uid']) && !isset($_POST['nombre'])) {
        $uid_recibido = trim($_POST['uid']);
        file_put_contents($archivo_uid, $uid_recibido);
        echo "UID_RECEIVED_BY_SERVER";
        exit;
    }

    // CASO 2: Formulario web
    if (isset($_POST['nombre']) && isset($_POST['uid'])) {
        $nombre    = $_POST['nombre'];
        $apellido  = $_POST['apellido'];
        $documento = $_POST['documento'];
        $cargo     = $_POST['cargo'];
        $uid       = $_POST['uid'];

        try {
            $conexion->beginTransaction();

            $foto_nombre = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                if (!in_array(strtolower($extension), ['jpg','jpeg','png'])) {
                    throw new Exception("Formato de imagen no permitido.");
                }
                $foto_nombre  = uniqid('emp_', true) . '.' . $extension;
                $ruta_destino = '/var/www/html/fotos/' . $foto_nombre;
                if (!is_dir('/var/www/html/fotos')) mkdir('/var/www/html/fotos', 0777, true);
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                    throw new Exception("Error al guardar la foto.");
                }
            }

            $stmtU = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, documento, cargo, ruta_foto) VALUES (?, ?, ?, ?, ?)");
            $stmtU->execute([$nombre, $apellido, $documento, $cargo, $foto_nombre]);
            $id_usuario = $conexion->lastInsertId();

            $stmtT = $conexion->prepare("INSERT INTO tarjetas (uid_rfid, id_usuario, estado) VALUES (?, ?, 'activa')");
            $stmtT->execute([$uid, $id_usuario]);

            $conexion->commit();
            $mensaje  = "Usuario y tarjeta registrados con éxito.";
            $tipo_msg = "ok";
            file_put_contents($archivo_uid, "");

        } catch (Exception $e) {
            $conexion->rollBack();
            $mensaje  = "Error al registrar: " . $e->getMessage();
            $tipo_msg = "err";
        }
    }
}

$uid_actual = file_exists($archivo_uid) ? trim(file_get_contents($archivo_uid)) : "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Heimdall | Registro</title>
  <?php include 'nav.php'; ?>
  <style>
    .form-wrap { max-width: 520px; margin: 0 auto; }
    .form-card { padding: 44px 40px; }

    .uid-field {
      background: rgba(201,168,76,0.06);
      border: 1px solid rgba(201,168,76,0.3);
      border-radius: 3px;
      padding: 16px;
      margin-bottom: 20px;
    }
    .uid-field label {
      font-size: 0.68rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
      display: block;
      margin-bottom: 8px;
    }
    .uid-field input {
      width: 100%;
      padding: 10px 12px;
      background: transparent;
      border: none;
      border-bottom: 1px solid rgba(201,168,76,0.3);
      color: var(--text);
      font-family: 'Raleway', monospace;
      font-size: 1rem;
      letter-spacing: 3px;
      outline: none;
    }
    .uid-hint {
      font-size: 0.68rem;
      color: var(--text-muted);
      margin-top: 8px;
      letter-spacing: 1px;
    }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .file-label {
      display: block;
      font-size: 0.68rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text-muted);
      margin-bottom: 7px;
    }
    input[type="file"] {
      width: 100%;
      padding: 10px 0;
      color: var(--text-muted);
      font-size: 0.82rem;
      cursor: pointer;
    }
    input[type="file"]::file-selector-button {
      background: transparent;
      border: 1px solid rgba(201,168,76,0.3);
      color: var(--gold);
      padding: 6px 14px;
      font-size: 0.72rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      cursor: pointer;
      margin-right: 12px;
      border-radius: 2px;
      transition: background 0.2s;
    }
    input[type="file"]::file-selector-button:hover { background: var(--gold-glow); }
  </style>
</head>
<body>
<div class="page-wrapper">
  <h1 class="page-title">Alta de Personal</h1>
  <p class="page-subtitle">Registro de nuevo usuario y tarjeta RFID</p>

  <div class="form-wrap">
    <?php if ($mensaje): ?>
      <div class="msg-<?= $tipo_msg ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="card form-card">
      <form method="POST" enctype="multipart/form-data">

        <div class="two-col">
          <div class="field-group">
            <label>Nombre</label>
            <input type="text" name="nombre" required>
          </div>
          <div class="field-group">
            <label>Apellido</label>
            <input type="text" name="apellido" required>
          </div>
        </div>

        <div class="two-col">
          <div class="field-group">
            <label>DNI / Documento</label>
            <input type="text" name="documento" maxlength="9" required>
          </div>
          <div class="field-group">
            <label>Cargo</label>
            <input type="text" name="cargo">
          </div>
        </div>

        <div class="field-group">
          <label class="file-label">Fotografía del empleado</label>
          <input type="file" name="foto" accept="image/jpeg,image/png" required>
        </div>

        <div class="uid-field">
          <label>UID Tarjeta RFID</label>
          <input type="text" name="uid"
                 value="<?= htmlspecialchars($uid_actual) ?>"
                 placeholder="Pasa la tarjeta en modo registro..." required readonly>
          <p class="uid-hint">
            <?= $uid_actual ? '✓ Tarjeta detectada — ' . htmlspecialchars($uid_actual) : 'Sin tarjeta detectada. Usa el ESP32 en modo REGISTRO y recarga la página.' ?>
          </p>
        </div>

        <button type="submit" class="btn-gold">Guardar Registro</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
