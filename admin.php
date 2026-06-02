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

$msg_post   = '';
$err_post   = '';
$msg_foto   = '';
$err_foto   = '';
$msg_perfil = '';
$err_perfil = '';

// ----------------------------------------------------------------
// Editar perfil
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'editar_perfil') {
    csrf_verify();

    $p_nombre      = trim($_POST['nombre']      ?? '');
    $p_dni         = trim($_POST['dni']         ?? '');
    $p_carrera     = trim($_POST['carrera']     ?? '');
    $p_materia     = trim($_POST['materia']     ?? '');
    $p_legajo      = trim($_POST['legajo']      ?? '');
    $p_descripcion = trim($_POST['descripcion'] ?? '');

    $pdo  = get_pdo();
    $stmt = $pdo->prepare(
        'UPDATE perfil
            SET nombre=:nombre, dni=:dni, carrera=:carrera,
                materia=:materia, legajo=:legajo, descripcion=:descripcion
          WHERE id=1'
    );
    $stmt->execute([
        ':nombre'      => $p_nombre,
        ':dni'         => $p_dni,
        ':carrera'     => $p_carrera,
        ':materia'     => $p_materia,
        ':legajo'      => $p_legajo,
        ':descripcion' => $p_descripcion,
    ]);
    $msg_perfil = 'Perfil actualizado correctamente.';
}

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

// ----------------------------------------------------------------
// Subida / reemplazo de foto personal
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'subir_foto') {
    csrf_verify();

    $upload_err = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($upload_err !== UPLOAD_ERR_OK) {
        $err_foto = 'Error al recibir el archivo (código ' . (int)$upload_err . ').';
    } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        $err_foto = 'La imagen no puede superar 2 MB.';
    } else {
        $tmp = $_FILES['foto']['tmp_name'];

        // Validar MIME real con finfo
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($tmp);

        $permitidos = ['image/jpeg', 'image/png'];

        if (!in_array($mimeReal, $permitidos, true)) {
            $err_foto = 'Solo se aceptan imágenes JPG o PNG.';
        } elseif (getimagesize($tmp) === false) {
            $err_foto = 'El archivo no es una imagen válida.';
        } else {
            $destino = UPLOAD_DIR . PHOTO_FILE;
            if (move_uploaded_file($tmp, $destino)) {
                $msg_foto = 'Foto actualizada correctamente.';
            } else {
                $err_foto = 'No se pudo guardar la imagen. Verificá los permisos de uploads/.';
            }
        }
    }
}
// Cargamos el perfil actual para precargar el formulario (después de cualquier UPDATE)
$perfil = get_perfil();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<header>
    <div class="header-inner">
        <h1>Panel de administración</h1>
        <nav>
            <a href="/">← Ver blog</a>
            &nbsp;|&nbsp;
            <a href="/logout.php">Cerrar sesión</a>
        </nav>
    </div>
</header>

<main>

    <!-- ============================
         Formulario: Editar perfil
         ============================ -->
    <section class="admin-section">
        <h2>Datos personales</h2>

        <?php if ($msg_perfil !== ''): ?>
            <p class="msg-ok"><?= htmlspecialchars($msg_perfil, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($err_perfil !== ''): ?>
            <p class="msg-error"><?= htmlspecialchars($err_perfil, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="/admin.php">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="editar_perfil">
            <label>
                Nombre completo
                <input type="text" name="nombre" maxlength="200"
                       value="<?= htmlspecialchars($perfil['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                DNI
                <input type="text" name="dni" maxlength="20"
                       value="<?= htmlspecialchars($perfil['dni'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Carrera
                <input type="text" name="carrera" maxlength="200"
                       value="<?= htmlspecialchars($perfil['carrera'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Materia
                <input type="text" name="materia" maxlength="200"
                       value="<?= htmlspecialchars($perfil['materia'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Legajo
                <input type="text" name="legajo" maxlength="50"
                       value="<?= htmlspecialchars($perfil['legajo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Descripción / presentación (opcional)
                <textarea name="descripcion" rows="4"><?= htmlspecialchars($perfil['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>
            <button type="submit">Guardar perfil</button>
        </form>
    </section>

    <!-- ============================
         Formulario: Nuevo post
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
            <button type="submit">Publicar</button>
        </form>
    </section>

    <!-- ============================
         Formulario: Subir foto
         ============================ -->
    <section class="admin-section">
        <h2>Foto personal</h2>

        <?php if (file_exists(UPLOAD_DIR . PHOTO_FILE)): ?>
            <p>Foto actual:</p>
            <img src="/uploads/<?= htmlspecialchars(PHOTO_FILE, ENT_QUOTES, 'UTF-8') ?>?t=<?= time() ?>"
                 alt="Foto actual" class="foto-preview">
        <?php else: ?>
            <p class="empty">No hay foto cargada aún.</p>
        <?php endif; ?>

        <?php if ($msg_foto !== ''): ?>
            <p class="msg-ok"><?= htmlspecialchars($msg_foto, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($err_foto !== ''): ?>
            <p class="msg-error"><?= htmlspecialchars($err_foto, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="/admin.php" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="subir_foto">
            <label>
                Seleccioná una imagen (JPG o PNG, máx. 2 MB)
                <input type="file" name="foto" accept="image/jpeg,image/png" required>
            </label>
            <button type="submit">Subir / reemplazar foto</button>
        </form>
    </section>

</main>

</body>
</html>
