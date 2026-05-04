<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Filtros opcionales
$filtro_tipo   = $_GET['tipo']   ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$filtro_fecha  = $_GET['fecha']  ?? '';

$where   = [];
$params  = [];

if ($filtro_tipo)   { $where[] = "a.tipo_acceso = ?";       $params[] = $filtro_tipo; }
if ($filtro_estado !== '') { $where[] = "a.acceso_concedido = ?"; $params[] = $filtro_estado; }
if ($filtro_fecha)  { $where[] = "DATE(a.fecha_hora) = ?";  $params[] = $filtro_fecha; }

$sql_where = $where ? "WHERE " . implode(" AND ", $where) : "";

try {
    $sql = "SELECT a.id_acceso, a.fecha_hora, a.tipo_acceso, a.acceso_concedido,
                   t.uid_rfid, u.nombre, u.apellido, u.cargo
            FROM accesos a
            LEFT JOIN tarjetas t ON a.uid_rfid = t.uid_rfid
            LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
            $sql_where
            ORDER BY a.fecha_hora DESC
            LIMIT 500";
    $stmt   = $conexion->prepare($sql);
    $stmt->execute($params);
    $accesos = $stmt->fetchAll();

    // Totales para el resumen
    $total    = count($accesos);
    $concedidos = count(array_filter($accesos, fn($a) => $a['acceso_concedido']));
    $denegados  = $total - $concedidos;
} catch (PDOException $e) {
    $accesos = [];
    $total = $concedidos = $denegados = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Heimdall | Historial</title>
  <?php include 'nav.php'; ?>
  <style>
    /* ── STATS BAR ── */
    .stats-bar {
      display: flex;
      gap: 16px;
      margin-bottom: 30px;
    }
    .stat-box {
      flex: 1;
      padding: 20px 24px;
      border-radius: 3px;
      background: var(--card);
      border: 1px solid var(--card-border);
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .stat-box .stat-num {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      color: var(--gold);
    }
    .stat-box .stat-label {
      font-size: 0.65rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text-muted);
    }
    .stat-box.ok  .stat-num { color: #4caf50; }
    .stat-box.err .stat-num { color: #ef5350; }

    /* ── FILTROS ── */
    .filtros {
      display: flex;
      gap: 12px;
      margin-bottom: 24px;
      flex-wrap: wrap;
      align-items: flex-end;
    }
    .filtros .field-group { margin-bottom: 0; flex: 1; min-width: 140px; }
    .filtros select, .filtros input[type="date"] {
      width: 100%;
      padding: 9px 12px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(201,168,76,0.2);
      border-radius: 2px;
      color: var(--text);
      font-family: 'Raleway', sans-serif;
      font-size: 0.82rem;
      outline: none;
      transition: border-color 0.2s;
    }
    .filtros select:focus, .filtros input[type="date"]:focus { border-color: var(--gold); }
    .filtros select option { background: #111; }
    .btn-filter {
      padding: 9px 22px;
      background: transparent;
      border: 1px solid rgba(201,168,76,0.35);
      color: var(--gold);
      font-family: 'Raleway', sans-serif;
      font-size: 0.72rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      cursor: pointer;
      border-radius: 2px;
      transition: background 0.2s;
      white-space: nowrap;
    }
    .btn-filter:hover { background: var(--gold-glow); }

    /* ── TABLA EXPLORADOR ── */
    .explorer {
      width: 100%;
      border-collapse: collapse;
    }
    .explorer thead th {
      padding: 10px 16px;
      font-size: 0.65rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text-muted);
      text-align: left;
      border-bottom: 1px solid var(--card-border);
      background: rgba(201,168,76,0.05);
      user-select: none;
    }
    .explorer tbody tr {
      border-bottom: 1px solid rgba(255,255,255,0.04);
      transition: background 0.15s;
      cursor: default;
    }
    .explorer tbody tr:hover { background: rgba(201,168,76,0.05); }
    .explorer tbody td {
      padding: 11px 16px;
      font-size: 0.84rem;
      color: var(--text);
      vertical-align: middle;
    }
    .explorer tbody td.muted { color: var(--text-muted); font-size: 0.78rem; }

    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 2px;
      font-size: 0.65rem;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      font-weight: 700;
    }
    .badge-ok  { background: rgba(76,175,80,0.15);  color: #66bb6a; border: 1px solid #4caf5044; }
    .badge-err { background: rgba(239,83,80,0.15);  color: #ef9a9a; border: 1px solid #ef535044; }
    .badge-in  { background: rgba(201,168,76,0.12); color: var(--gold); border: 1px solid var(--card-border); }
    .badge-out { background: rgba(100,100,120,0.2); color: #90a4ae; border: 1px solid #44444466; }

    .icon-row { display: flex; align-items: center; gap: 10px; }
    .avatar-mini {
      width: 32px; height: 32px;
      border-radius: 50%;
      background: rgba(201,168,76,0.12);
      border: 1px solid var(--card-border);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Playfair Display', serif;
      font-size: 0.85rem;
      color: var(--gold);
      flex-shrink: 0;
    }

    .explorer-card { padding: 0; overflow: hidden; }
    .empty-state { padding: 60px; text-align: center; color: var(--text-muted); font-size: 0.82rem; letter-spacing: 2px; text-transform: uppercase; }
  </style>
</head>
<body>
<div class="page-wrapper">
  <h1 class="page-title">Historial de Accesos</h1>
  <p class="page-subtitle">Registro completo de entradas y salidas</p>

  <!-- Stats -->
  <div class="stats-bar">
    <div class="stat-box">
      <span class="stat-num"><?= $total ?></span>
      <span class="stat-label">Total registros</span>
    </div>
    <div class="stat-box ok">
      <span class="stat-num"><?= $concedidos ?></span>
      <span class="stat-label">Concedidos</span>
    </div>
    <div class="stat-box err">
      <span class="stat-num"><?= $denegados ?></span>
      <span class="stat-label">Denegados</span>
    </div>
  </div>

  <!-- Filtros -->
  <form method="GET" class="filtros">
    <div class="field-group">
      <label>Tipo</label>
      <select name="tipo">
        <option value="">Todos</option>
        <option value="entrada" <?= $filtro_tipo==='entrada'?'selected':'' ?>>Entrada</option>
        <option value="salida"  <?= $filtro_tipo==='salida' ?'selected':'' ?>>Salida</option>
      </select>
    </div>
    <div class="field-group">
      <label>Estado</label>
      <select name="estado">
        <option value="">Todos</option>
        <option value="1" <?= $filtro_estado==='1'?'selected':'' ?>>Concedido</option>
        <option value="0" <?= $filtro_estado==='0'?'selected':'' ?>>Denegado</option>
      </select>
    </div>
    <div class="field-group">
      <label>Fecha</label>
      <input type="date" name="fecha" value="<?= htmlspecialchars($filtro_fecha) ?>">
    </div>
    <button type="submit" class="btn-filter">Filtrar</button>
    <a href="historial.php" class="btn-filter" style="text-decoration:none">Limpiar</a>
  </form>

  <!-- Tabla -->
  <div class="card explorer-card">
    <?php if ($accesos): ?>
    <table class="explorer">
      <thead>
        <tr>
          <th>#</th>
          <th>Usuario</th>
          <th>UID Tarjeta</th>
          <th>Tipo</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th>Hora</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($accesos as $a): ?>
        <tr>
          <td class="muted"><?= $a['id_acceso'] ?></td>
          <td>
            <div class="icon-row">
              <div class="avatar-mini">
                <?= $a['nombre'] ? strtoupper(substr($a['nombre'],0,1) . substr($a['apellido'],0,1)) : '?' ?>
              </div>
              <div>
                <div><?= $a['nombre'] ? htmlspecialchars($a['nombre'] . ' ' . $a['apellido']) : '<span style="color:var(--text-muted)">Desconocido</span>' ?></div>
                <?php if ($a['cargo']): ?>
                  <div style="font-size:0.72rem;color:var(--text-muted)"><?= htmlspecialchars($a['cargo']) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td class="muted" style="font-family:monospace;letter-spacing:1px"><?= htmlspecialchars($a['uid_rfid']) ?></td>
          <td>
            <?php if ($a['tipo_acceso'] === 'entrada'): ?>
              <span class="badge badge-in">↑ Entrada</span>
            <?php else: ?>
              <span class="badge badge-out">↓ Salida</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($a['acceso_concedido']): ?>
              <span class="badge badge-ok">✓ Concedido</span>
            <?php else: ?>
              <span class="badge badge-err">✗ Denegado</span>
            <?php endif; ?>
          </td>
          <td class="muted"><?= date("d/m/Y", strtotime($a['fecha_hora'])) ?></td>
          <td class="muted"><?= date("H:i:s", strtotime($a['fecha_hora'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state">No hay registros que mostrar</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
