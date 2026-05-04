<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $sql = "SELECT u.nombre, u.apellido, u.cargo, u.ruta_foto, a.fecha_hora, t.uid_rfid
            FROM accesos a
            JOIN tarjetas t ON a.uid_rfid = t.uid_rfid
            JOIN usuarios u ON t.id_usuario = u.id_usuario
            WHERE a.acceso_concedido = 1
            ORDER BY a.fecha_hora DESC
            LIMIT 1";
    $stmt  = $conexion->query($sql);
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
  <title>Heimdall | Panel</title>
  <?php include 'nav.php'; ?>
  <style>
    .panel-center {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding-top: 20px;
    }
    .access-card {
      width: 360px;
      padding: 44px 36px;
      text-align: center;
    }
    .photo-ring {
      width: 160px;
      height: 160px;
      border-radius: 50%;
      border: 3px solid var(--gold);
      overflow: hidden;
      margin: 0 auto 24px;
      background: #111;
      box-shadow: 0 0 30px var(--gold-glow);
    }
    .photo-ring img { width: 100%; height: 100%; object-fit: cover; }
    .status-badge {
      display: inline-block;
      font-size: 0.68rem;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #4caf50;
      border: 1px solid #4caf5055;
      padding: 5px 16px;
      border-radius: 2px;
      margin-bottom: 16px;
    }
    .access-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.7rem;
      color: var(--text);
      margin-bottom: 6px;
    }
    .access-cargo {
      font-size: 0.7rem;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 28px;
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-top: 1px solid rgba(201,168,76,0.12);
      font-size: 0.82rem;
    }
    .info-row span:first-child { color: var(--text-muted); letter-spacing: 1px; }
    .info-row span:last-child  { color: var(--text); font-weight: 600; }
    .no-data {
      text-align: center;
      padding: 60px 40px;
      color: var(--text-muted);
    }
    .no-data p { font-size: 0.85rem; letter-spacing: 2px; text-transform: uppercase; margin-top: 16px; }
    .pulse {
      width: 12px; height: 12px;
      border-radius: 50%;
      background: var(--gold);
      margin: 0 auto;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%,100% { box-shadow: 0 0 0 0 var(--gold-glow); }
      50%      { box-shadow: 0 0 0 12px transparent; }
    }
  </style>
</head>
<body>
<div class="page-wrapper">
  <h1 class="page-title">Panel de Control</h1>
  <p class="page-subtitle">Último acceso registrado</p>

  <div class="panel-center">
    <?php if (!empty($ultimo)): ?>
      <div class="card access-card">
        <div class="photo-ring">
          <img src="fotos/<?= htmlspecialchars($ultimo['ruta_foto']) ?>" alt="Foto">
        </div>
        <div class="status-badge">● Acceso Concedido</div>
        <div class="access-name"><?= htmlspecialchars($ultimo['nombre']) . ' ' . htmlspecialchars($ultimo['apellido']) ?></div>
        <div class="access-cargo"><?= htmlspecialchars($ultimo['cargo']) ?></div>
        <div class="info-row">
          <span>UID Tarjeta</span>
          <span><?= htmlspecialchars($ultimo['uid_rfid']) ?></span>
        </div>
        <div class="info-row">
          <span>Hora</span>
          <span><?= date("H:i:s", strtotime($ultimo['fecha_hora'])) ?></span>
        </div>
        <div class="info-row">
          <span>Fecha</span>
          <span><?= date("d/m/Y", strtotime($ultimo['fecha_hora'])) ?></span>
        </div>
      </div>
    <?php else: ?>
      <div class="card access-card no-data">
        <div class="pulse"></div>
        <p>Esperando lectura de tarjeta...</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  let ultimaFecha = "";
  function actualizarDashboard() {
    fetch('ajax_accesos.php')
      .then(r => r.json())
      .then(data => {
        if (data && data.fecha_hora !== ultimaFecha) {
          ultimaFecha = data.fecha_hora;
          document.querySelector('.panel-center').innerHTML = `
            <div class="card access-card">
              <div class="photo-ring">
                <img src="fotos/${data.ruta_foto}" alt="Foto">
              </div>
              <div class="status-badge">● Acceso Concedido</div>
              <div class="access-name">${data.nombre} ${data.apellido}</div>
              <div class="access-cargo">${data.cargo}</div>
              <div class="info-row"><span>Hora</span><span>${data.fecha_hora.split(' ')[1]}</span></div>
              <div class="info-row"><span>Fecha</span><span>${data.fecha_hora.split(' ')[0]}</span></div>
            </div>`;
        }
      })
      .catch(() => {});
  }
  setInterval(actualizarDashboard, 1500);
</script>
</body>
</html>
