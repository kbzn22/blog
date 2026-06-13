<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$pdo  = get_pdo();
$stmt = $pdo->prepare('SELECT id, titulo, contenido, fecha FROM posts ORDER BY fecha DESC');
$stmt->execute();
$posts = $stmt->fetchAll();

$tiene_foto = file_exists(ASSETS_DIR . 'perfil.jpg');

function fecha_es(string $fecha): string {
    static $meses = [
        1=>'enero', 2=>'febrero', 3=>'marzo',    4=>'abril',
        5=>'mayo',  6=>'junio',   7=>'julio',    8=>'agosto',
        9=>'septiembre', 10=>'octubre', 11=>'noviembre', 12=>'diciembre',
    ];
    $ts = strtotime($fecha);
    if ($ts === false) return htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
    return intval(date('d', $ts)) . ' de ' . $meses[intval(date('n', $ts))] . ' de ' . date('Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(NOMBRE, ENT_QUOTES, 'UTF-8') ?> – Blog</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<header class="hero">
    <div class="hero-inner">
        <div class="avatar-wrap">
            <?php if ($tiene_foto): ?>
                <img src="/assets/perfil.jpg"
                     alt="Foto de <?= htmlspecialchars(NOMBRE, ENT_QUOTES, 'UTF-8') ?>"
                     class="avatar">
            <?php else: ?>
                <div class="avatar avatar-placeholder" aria-hidden="true">
                    <svg viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="40" cy="30" r="18" fill="#475569"/>
                        <ellipse cx="40" cy="72" rx="28" ry="18" fill="#475569"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>

        <div class="hero-text">
            <h1><?= htmlspecialchars(NOMBRE, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="rol"><?= htmlspecialchars(ROL, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="descripcion"><?= htmlspecialchars(DESCRIPCION, ENT_QUOTES, 'UTF-8') ?></p>
            <div class="hero-meta">
                <span>&#127891; <?= htmlspecialchars(CARRERA, ENT_QUOTES, 'UTF-8') ?></span>
                <span>&#127963; <?= htmlspecialchars(UNIVERSIDAD, ENT_QUOTES, 'UTF-8') ?></span>
                <span>&#9993; <?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <nav class="hero-nav">
        <a href="/informe.pdf" target="_blank">
            <svg class="icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M4 4h8l4 4v8a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z"/>
                <polyline points="12,4 12,8 16,8"/>
            </svg>
            Ver Informe PDF
        </a>
        <a href="/login.php">Panel Admin</a>
    </nav>
</header>

<main>
    <h2 class="section-title">Posts</h2>

    <?php if (empty($posts)): ?>
        <p class="empty">No hay posts publicados todavía.</p>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <h3><?= htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <time datetime="<?= htmlspecialchars($post['fecha'], ENT_QUOTES, 'UTF-8') ?>">
                        &#128197; <?= fecha_es($post['fecha']) ?>
                    </time>
                    <div class="post-contenido">
                        <?= nl2br(htmlspecialchars($post['contenido'], ENT_QUOTES, 'UTF-8')) ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(NOMBRE, ENT_QUOTES, 'UTF-8') ?></p>
    <p>
        <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
        &nbsp;&middot;&nbsp;
        <a href="/informe.pdf" target="_blank">Informe PDF</a>
        &nbsp;&middot;&nbsp;
        <a href="/login.php">Admin</a>
    </p>
</footer>

</body>
</html>
