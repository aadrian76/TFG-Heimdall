<?php
// Detecta qué página está activa para resaltarla en el nav
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">

<style>
  :root {
    --gold:       #c9a84c;
    --gold-dark:  #9a7a30;
    --gold-glow:  rgba(201,168,76,0.25);
    --bg:         #080808;
    --card:       rgba(12,12,12,0.88);
    --card-border:rgba(201,168,76,0.25);
    --text:       #f0ead8;
    --text-muted: #8a8070;
    --danger:     #c0392b;
    --success:    #1a7a4a;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Raleway', sans-serif;
    background-color: var(--bg);
    /* ───────────────────────────────────────────────────────────────
       IMAGEN DE FONDO: coloca tu imagen en src/img/bg.jpg
       y descomenta la siguiente línea:
       background-image: url('img/bg.jpg');
    ─────────────────────────────────────────────────────────────── */
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: var(--text);
    min-height: 100vh;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.72);
    pointer-events: none;
    z-index: 0;
  }

  /* ── NAVBAR ── */
  .navbar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(4,4,4,0.95);
    border-bottom: 1px solid var(--card-border);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    height: 64px;
  }

  .navbar-brand {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: var(--gold);
    letter-spacing: 4px;
    text-decoration: none;
    text-transform: uppercase;
  }

  .navbar-brand span {
    font-weight: 400;
    color: var(--text-muted);
    font-size: 0.65rem;
    display: block;
    letter-spacing: 6px;
    margin-top: -4px;
  }

  .nav-links {
    display: flex;
    gap: 8px;
    list-style: none;
  }

  .nav-links a {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 8px 18px;
    border-radius: 3px;
    text-decoration: none;
    color: var(--text-muted);
    font-size: 0.78rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-weight: 600;
    border: 1px solid transparent;
    transition: all 0.25s;
  }

  .nav-links a:hover,
  .nav-links a.active {
    color: var(--gold);
    border-color: var(--card-border);
    background: var(--gold-glow);
  }

  .nav-links a svg {
    width: 14px;
    height: 14px;
    opacity: 0.7;
  }

  /* ── PÁGINA WRAPPER ── */
  .page-wrapper {
    position: relative;
    z-index: 1;
    padding: 50px 40px;
    max-width: 1200px;
    margin: 0 auto;
  }

  .page-title {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    color: var(--gold);
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  .page-subtitle {
    color: var(--text-muted);
    font-size: 0.75rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--card-border);
  }

  /* ── CARD BASE ── */
  .card {
    background: var(--card);
    border: 1px solid var(--card-border);
    border-radius: 4px;
    backdrop-filter: blur(12px);
  }

  /* ── BOTÓN GOLD ── */
  .btn-gold {
    background: var(--gold);
    color: #000;
    border: none;
    padding: 12px 28px;
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    border-radius: 2px;
    transition: background 0.2s, box-shadow 0.2s;
    width: 100%;
  }

  .btn-gold:hover {
    background: var(--gold-dark);
    box-shadow: 0 0 20px var(--gold-glow);
  }

  /* ── INPUTS ── */
  .field-group { margin-bottom: 20px; }

  .field-group label {
    display: block;
    font-size: 0.7rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 7px;
  }

  .field-group input,
  .field-group select {
    width: 100%;
    padding: 11px 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(201,168,76,0.2);
    border-radius: 2px;
    color: var(--text);
    font-family: 'Raleway', sans-serif;
    font-size: 0.9rem;
    transition: border-color 0.2s;
    outline: none;
  }

  .field-group input:focus,
  .field-group select:focus {
    border-color: var(--gold);
  }

  .field-group input[readonly] {
    opacity: 0.6;
    cursor: not-allowed;
  }

  /* ── MENSAJES ── */
  .msg-ok  { color: #4caf50; font-size: 0.82rem; letter-spacing: 1px; padding: 12px; border: 1px solid #4caf5044; border-radius: 3px; margin-bottom: 20px; }
  .msg-err { color: #ef5350; font-size: 0.82rem; letter-spacing: 1px; padding: 12px; border: 1px solid #ef535044; border-radius: 3px; margin-bottom: 20px; }

  /* ── GOLD DIVIDER ── */
  .gold-line {
    width: 60px;
    height: 2px;
    background: var(--gold);
    margin: 10px 0 30px;
  }
</style>

<nav class="navbar">
  <a class="navbar-brand" href="index.php">
    HEIMDALL
    <span>Sistema de Control</span>
  </a>
  <ul class="nav-links">
    <li>
      <a href="index.php" class="<?= $pagina_actual === 'index.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Panel
      </a>
    </li>
    <li>
      <a href="registrar.php" class="<?= $pagina_actual === 'registrar.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        Registrar
      </a>
    </li>
    <li>
      <a href="historial.php" class="<?= $pagina_actual === 'historial.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Historial
      </a>
    </li>
    <li>
      <a href="registro_de_tarjetas.php" class="<?= $pagina_actual === 'registro_de_tarjetas.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        Tarjetas
      </a>
    </li>
    <li>
      <a href="logout.php" style="--hover-color: #ef5350; --hover-border: rgba(239,83,80,0.35); --hover-bg: rgba(239,83,80,0.1);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Cerrar Sesión
      </a>
    </li>
  </ul>
</nav>
