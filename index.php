<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$perfil = get_perfil();

$pdo  = get_pdo();
$stmt = $pdo->prepare('SELECT id, titulo, contenido, fecha FROM posts ORDER BY fecha DESC');
$stmt->execute();
$posts = $stmt->fetchAll();

$foto_url   = '/uploads/' . PHOTO_FILE;
$tiene_foto = file_exists(UPLOAD_DIR . PHOTO_FILE);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($perfil['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?> – Blog</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<header>
    <div class="header-inner">
        <?php if ($tiene_foto): ?>
            <img src="<?= htmlspecialchars($foto_url, ENT_QUOTES, 'UTF-8') ?>"
                 alt="Foto personal" class="foto-personal">
        <?php endif; ?>
        <div class="datos-personales">
            <h1><?= htmlspecialchars($perfil['nombre']  ?? '-', ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if (!empty($perfil['dni'])): ?>
            <p><strong>DNI:</strong> <?= htmlspecialchars($perfil['dni'],      ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($perfil['carrera'])): ?>
            <p><strong>Carrera:</strong> <?= htmlspecialchars($perfil['carrera'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($perfil['materia'])): ?>
            <p><strong>Materia:</strong> <?= htmlspecialchars($perfil['materia'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($perfil['legajo'])): ?>
            <p><strong>Legajo:</strong> <?= htmlspecialchars($perfil['legajo'],   ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($perfil['descripcion'])): ?>
            <p class="descripcion"><?= nl2br(htmlspecialchars($perfil['descripcion'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <nav>
        <a href="/informe.pdf" target="_blank">📄 Ver Informe (PDF)</a>
        &nbsp;|&nbsp;
        <a href="/login.php">Panel Admin</a>
    </nav>
</header>

<main>
    <h2>Posts</h2>
    <?php if (empty($posts)): ?>
        <p class="empty">No hay posts publicados todavía.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="post">
                <h3><?= htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                <time datetime="<?= htmlspecialchars($post['fecha'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($post['fecha'], ENT_QUOTES, 'UTF-8') ?>
                </time>
                <div class="contenido">
                    <?= nl2br(htmlspecialchars($post['contenido'], ENT_QUOTES, 'UTF-8')) ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($perfil['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
</footer>

</body>
</html>
