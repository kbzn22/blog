<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

$msg_post = '';
$err_post = '';

// ----------------------------------------------------------------
// Alta de post
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'nuevo_post') {
    csrf_verify();

    $titulo    = trim($_POST['titulo']    ?? '');
    $contenido = trim($_POST['contenido'] ?? '');

    if ($titulo === '' || $contenido === '') {
        $err_post = 'El título y el contenido son obligatorios.';
    } elseif (mb_strlen($titulo) > 200) {
        $err_post = 'El título no puede superar 200 caracteres.';
    } else {
        $pdo  = get_pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO posts (titulo, contenido) VALUES (:titulo, :contenido)'
        );
        $stmt->execute([':titulo' => $titulo, ':contenido' => $contenido]);
        $msg_post = 'Post publicado correctamente.';
    }
}

// Listar posts recientes
$pdo  = get_pdo();
$stmt = $pdo->prepare('SELECT id, titulo, fecha FROM posts ORDER BY fecha DESC LIMIT 20');
$stmt->execute();
$posts_recientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="admin-body">

<header class="admin-header">
    <div class="admin-header-inner">
        <h1>Panel de administración</h1>
        <nav>
            <a href="/">&#8592; Ver blog</a>
            <a href="/logout.php">Cerrar sesión</a>
        </nav>
    </div>
</header>

<main class="admin-main">

    <!-- ============================
         Publicar nuevo post
         ============================ -->
    <section class="admin-section">
        <h2>Publicar nuevo post</h2>

        <?php if ($msg_post !== ''): ?>
            <p class="msg-ok"><?= htmlspecialchars($msg_post, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($err_post !== ''): ?>
            <p class="msg-error"><?= htmlspecialchars($err_post, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="/admin.php">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="nuevo_post">
            <label>
                Título (máx. 200 caracteres)
                <input type="text" name="titulo" maxlength="200" required
                       value="<?= htmlspecialchars($_POST['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Contenido
                <textarea name="contenido" rows="8" required><?= htmlspecialchars($_POST['contenido'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <button type="submit">Publicar post</button>
        </form>
    </section>

    <!-- ============================
         Posts publicados
         ============================ -->
    <?php if (!empty($posts_recientes)): ?>
    <section class="admin-section">
        <h2>Posts publicados</h2>
        <ul class="posts-list">
            <?php foreach ($posts_recientes as $p): ?>
                <li>
                    <span class="post-titulo"><?= htmlspecialchars($p['titulo'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="post-fecha"><?= htmlspecialchars($p['fecha'], ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

</main>

</body>
</html>
