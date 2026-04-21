<?php
session_start();
include 'conexion.php';

// Si ya está logueado, lo mandamos al index
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
            // Buscamos al administrador por su nombre de usuario
            $stmt = $conexion->prepare("SELECT id, usuario_login, password_hash FROM administradores WHERE usuario_login = ?");
            $stmt->execute([$user]);
            $admin = $stmt->fetch();

            // Verificamos si existe y si la contraseña coincide (usando password_verify)
            if ($admin && password_verify($pass, $admin['password_hash'])) {
                // Creamos la sesión
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $admin['usuario_login'];

                // Actualizamos el último login (opcional, según tu tabla)
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
    <title>Heimdall | Login</title>
    <style>
        :root { --primary: #00d4ff; --bg: #121212; --card: #1e1e26; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: white; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        
        .login-card { background: var(--card); padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 350px; border-top: 3px solid var(--primary); }
        h2 { text-align: center; color: var(--primary); margin-bottom: 30px; letter-spacing: 2px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #aaa; font-size: 0.9rem; }
        input { width: 100%; padding: 12px; border-radius: 5px; border: 1px solid #333; background: #252525; color: white; box-sizing: border-box; }
        input:focus { border-color: var(--primary); outline: none; }
        
        button { width: 100%; padding: 12px; border: none; border-radius: 5px; background: var(--primary); color: #000; font-weight: bold; cursor: pointer; font-size: 1rem; transition: 0.3s; }
        button:hover { background: #008cb3; }
        
        .error-msg { background: rgba(255, 0, 0, 0.2); color: #ff6b6b; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 0.85rem; text-align: center; }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>HEIMDALL</h2>
        
        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Usuario Administrador</label>
                <input type="text" name="usuario" required autofocus>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">ENTRAR</button>
        </form>
    </div>

</body>
</html>