<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
session_start();

// Ya autenticado: ir directo al panel
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USER && password_verify($password, ADMIN_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: /admin.php');
        exit;
    }

    // Mismo mensaje para usuario inválido y contraseña incorrecta (no dar pistas)
    $error = 'Credenciales incorrectas.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login – Admin</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<main class="login-wrap">
    <h2>Acceso al panel</h2>

    <?php if ($error !== ''): ?>
        <p class="msg-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="/login.php">
        <?= csrf_field() ?>
        <label>
            Usuario
            <input type="text" name="username" autocomplete="username" required>
        </label>
        <label>
            Contraseña
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button type="submit">Ingresar</button>
    </form>

    <p><a href="/">← Volver al blog</a></p>
</main>

</body>
</html>
