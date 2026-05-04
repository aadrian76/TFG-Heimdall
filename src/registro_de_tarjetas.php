<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.documento, u.cargo,
                   u.ruta_foto, u.fecha_creacion,
                   t.uid_rfid, t.estado, t.fecha_asignacion
            FROM usuarios u
            LEFT JOIN tarjetas t ON t.id_usuario = u.id_usuario
            ORDER BY u.fecha_creacion DESC";
    $stmt    = $conexion->query($sql);
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $usuarios = [];
}

$total    = count($usuarios);
$activas  = count(array_filter($usuarios, fn($u) => $u['estado'] === 'activa'));
$inactivas = $total - $activas;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Heimdall | Tarjetas</title>
  <?php include 'nav.php'; ?>
  <style>
    /* ── STATS ── */
    .stats-bar { display: flex; gap: 16px; margin-bottom: 30px; }
    .stat-box {
      flex: 1; padding: 20px 24px; border-radius: 3px;
      background: var(--card); border: 1px solid var(--card-border);
      display: flex; flex-direction: column; gap: 4px;
    }
    .stat-box .stat-num  { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--gold); }
    .stat-box .stat-label { font-size: 0.65rem; letter-spacing: 2px; text-transform: uppercase; color: var(--text-muted); }
    .stat-box.ok  .stat-num { color: #4caf50; }
    .stat-box.err .stat-num { color: #ef5350; }

    /* ── SEARCH ── */
    .search-bar {
      display: flex; gap: 12px; margin-bottom: 24px;
    }
    .search-bar input {
      flex: 1; padding: 10px 16px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(201,168,76,0.2);
      border-radius: 2px; color: var(--text);
      font-family: 'Raleway', sans-serif; font-size: 0.88rem;
      outline: none; transition: border-color 0.2s;
    }
    .search-bar input:focus { border-color: var(--gold); }
    .search-bar input::placeholder { color: var(--text-muted); }

    /* ── GRID DE TARJETAS ── */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 16px;
    }

    .user-card {
      padding: 24px;
      border-radius: 4px;
      transition: border-color 0.2s, transform 0.2s;
      position: relative;
      overflow: hidden;
    }
    .user-card:hover {
      border-color: rgba(201,168,76,0.5);
      transform: translateY(-2px);
    }
    .user-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: var(--gold);
      opacity: 0;
      transition: opacity 0.2s;
    }
    .user-card:hover::before { opacity: 1; }

    .user-header { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
    .user-photo {
      width: 52px; height: 52px;
      border-radius: 50%;
      border: 2px solid var(--card-border);
      object-fit: cover;
      background: #111;
      flex-shrink: 0;
    }
    .user-photo-placeholder {
      width: 52px; height: 52px;
      border-radius: 50%;
      border: 2px solid var(--card-border);
      background: rgba(201,168,76,0.08);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Playfair Display', serif;
      font-size: 1.1rem;
      color: var(--gold);
      flex-shrink: 0;
    }
    .user-info-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.05rem;
      color: var(--text);
      line-height: 1.3;
    }
    .user-info-cargo {
      font-size: 0.68rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
      margin-top: 2px;
    }

    .data-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 8px 0;
      border-top: 1px solid rgba(255,255,255,0.05);
      font-size: 0.8rem;
    }
    .data-row .label { color: var(--text-muted); font-size: 0.68rem; letter-spacing: 1px; text-transform: uppercase; }
    .data-row .value { color: var(--text); font-weight: 500; }
    .data-row .uid   { font-family: monospace; font-size: 0.85rem; letter-spacing: 2px; color: var(--gold); }

    .badge {
      display: inline-block; padding: 2px 9px; border-radius: 2px;
      font-size: 0.62rem; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 700;
    }
    .badge-activa   { background: rgba(76,175,80,0.15);  color: #66bb6a; border: 1px solid #4caf5044; }
    .badge-inactiva { background: rgba(100,100,120,0.2); color: #90a4ae; border: 1px solid #44444466; }
    .badge-perdida  { background: rgba(239,83,80,0.15);  color: #ef9a9a; border: 1px solid #ef535044; }

    .no-card {
      font-size: 0.75rem; color: var(--text-muted); letter-spacing: 1px;
      font-style: italic; padding: 8px 0;
    }

    .empty-state { padding: 60px; text-align: center; color: var(--text-muted); font-size: 0.82rem; letter-spacing: 2px; text-transform: uppercase; }
  </style>
</head>
<body>
<div class="page-wrapper">
  <h1 class="page-title">Registro de Tarjetas</h1>
  <p class="page-subtitle">Usuarios registrados y sus tarjetas RFID asignadas</p>

  <!-- Stats -->
  <div class="stats-bar">
    <div class="stat-box">
      <span class="stat-num"><?= $total ?></span>
      <span class="stat-label">Total usuarios</span>
    </div>
    <div class="stat-box ok">
      <span class="stat-num"><?= $activas ?></span>
      <span class="stat-label">Tarjetas activas</span>
    </div>
    <div class="stat-box err">
      <span class="stat-num"><?= $inactivas ?></span>
      <span class="stat-label">Sin tarjeta / inactivas</span>
    </div>
  </div>

  <!-- Búsqueda -->
  <div class="search-bar">
    <input type="text" id="buscador" placeholder="Buscar por nombre, DNI o UID..." oninput="filtrar()">
  </div>

  <!-- Grid -->
  <?php if ($usuarios): ?>
  <div class="cards-grid" id="grid">
    <?php foreach ($usuarios as $u): ?>
    <div class="card user-card" data-search="<?= strtolower(htmlspecialchars($u['nombre'].' '.$u['apellido'].' '.$u['documento'].' '.$u['uid_rfid'])) ?>">
      <div class="user-header">
        <?php if ($u['ruta_foto']): ?>
          <img class="user-photo" src="fotos/<?= htmlspecialchars($u['ruta_foto']) ?>" alt="">
        <?php else: ?>
          <div class="user-photo-placeholder">
            <?= strtoupper(substr($u['nombre'],0,1) . substr($u['apellido'],0,1)) ?>
          </div>
        <?php endif; ?>
        <div>
          <div class="user-info-name"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></div>
          <div class="user-info-cargo"><?= htmlspecialchars($u['cargo'] ?: '—') ?></div>
        </div>
      </div>

      <div class="data-row">
        <span class="label">DNI</span>
        <span class="value"><?= htmlspecialchars($u['documento']) ?></span>
      </div>

      <?php if ($u['uid_rfid']): ?>
        <div class="data-row">
          <span class="label">UID Tarjeta</span>
          <span class="uid"><?= htmlspecialchars($u['uid_rfid']) ?></span>
        </div>
        <div class="data-row">
          <span class="label">Estado</span>
          <span class="badge badge-<?= $u['estado'] ?>"><?= ucfirst($u['estado']) ?></span>
        </div>
        <div class="data-row">
          <span class="label">Asignada</span>
          <span class="value"><?= date("d/m/Y", strtotime($u['fecha_asignacion'])) ?></span>
        </div>
      <?php else: ?>
        <p class="no-card">Sin tarjeta asignada</p>
      <?php endif; ?>

      <div class="data-row">
        <span class="label">Registro</span>
        <span class="value"><?= date("d/m/Y", strtotime($u['fecha_creacion'])) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="card empty-state">No hay usuarios registrados</div>
  <?php endif; ?>
</div>

<script>
  function filtrar() {
    const q = document.getElementById('buscador').value.toLowerCase();
    document.querySelectorAll('.user-card').forEach(card => {
      card.style.display = card.dataset.search.includes(q) ? '' : 'none';
    });
  }
</script>
</body>
</html>
