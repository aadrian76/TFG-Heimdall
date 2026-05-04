<?php
session_start();
include 'conexion.php';

if (isset($_SESSION['admin_id'])) {
    session_regenerate_id(true);
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['usuario'];
    $pass = $_POST['password'];
    if (!empty($user) && !empty($pass)) {
        try {
            $stmt = $conexion->prepare("SELECT id, usuario_login, password_hash FROM administradores WHERE usuario_login = ?");
            $stmt->execute([$user]);
            $admin = $stmt->fetch();
            if ($admin && password_verify($pass, $admin['password_hash'])) {
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_user'] = $admin['usuario_login'];
                $update = $conexion->prepare("UPDATE administradores SET ultimo_login = NOW() WHERE id = ?");
                $update->execute([$admin['id']]);
                header("Location: index.php");
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema. Intente más tarde.";
        }
    } else {
        $error = "Por favor, rellene todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Heimdall | Acceso</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --gold: #c9a84c; --gold-dark: #9a7a30; --gold-glow: rgba(201,168,76,0.25);
      --bg: #080808; --card: rgba(10,10,10,0.90); --text: #f0ead8; --text-muted: #8a8070;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Raleway', sans-serif;
      background-color: var(--bg);
      /* IMAGEN DE FONDO: coloca tu imagen en src/img/bg.jpg y descomenta:
         background-image: url('img/bg.jpg'); */
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.75);
      z-index: 0;
    }
    .login-wrap {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 380px;
      padding: 20px;
    }
    .login-card {
      background: var(--card);
      border: 1px solid rgba(201,168,76,0.25);
      border-top: 3px solid var(--gold);
      border-radius: 4px;
      padding: 50px 40px;
      backdrop-filter: blur(16px);
    }
    .logo {
      text-align: center;
      margin-bottom: 36px;
    }
    .logo h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2.2rem;
      color: var(--gold);
      letter-spacing: 6px;
      text-transform: uppercase;
    }
    .logo p {
      font-size: 0.65rem;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: var(--text-muted);
      margin-top: 4px;
    }
    .gold-line { width: 40px; height: 1px; background: var(--gold); margin: 14px auto; }
    .field-group { margin-bottom: 20px; }
    .field-group label {
      display: block;
      font-size: 0.68rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text-muted);
      margin-bottom: 7px;
    }
    .field-group input {
      width: 100%;
      padding: 12px 14px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(201,168,76,0.2);
      border-radius: 2px;
      color: var(--text);
      font-family: 'Raleway', sans-serif;
      font-size: 0.95rem;
      outline: none;
      transition: border-color 0.2s;
    }
    .field-group input:focus { border-color: var(--gold); }
    .btn-gold {
      width: 100%;
      padding: 13px;
      background: var(--gold);
      color: #000;
      border: none;
      font-family: 'Raleway', sans-serif;
      font-weight: 700;
      font-size: 0.75rem;
      letter-spacing: 3px;
      text-transform: uppercase;
      cursor: pointer;
      border-radius: 2px;
      margin-top: 10px;
      transition: background 0.2s, box-shadow 0.2s;
    }
    .btn-gold:hover { background: var(--gold-dark); box-shadow: 0 0 24px var(--gold-glow); }
    .error-msg {
      background: rgba(192,57,43,0.15);
      border: 1px solid rgba(192,57,43,0.4);
      color: #e57373;
      padding: 11px 14px;
      border-radius: 3px;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
      margin-bottom: 22px;
    }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <div class="logo">
        <h1>Heimdall</h1>
        <div class="gold-line"></div>
        <p>Control de Acceso</p>
      </div>
      <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="field-group">
          <label>Usuario Administrador</label>
          <input type="text" name="usuario" required autofocus>
        </div>
        <div class="field-group">
          <label>Contraseña</label>
          <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn-gold">Entrar</button>
      </form>
    </div>
  </div>
</body>
</html>
