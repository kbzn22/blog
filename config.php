<?php
// ============================================================
// BASE DE DATOS
// ============================================================
define('DB_HOST', '172.16.90.152');
define('DB_PORT', '5432');
define('DB_NAME', 'blogdb');
define('DB_USER', 'bloguser');
define('DB_PASS', 'ponerpsw');

// ============================================================
// CREDENCIALES ADMIN
// Para generar tu propio hash ejecutar DENTRO del contenedor:
//   php -r "echo password_hash('TU_PASSWORD', PASSWORD_BCRYPT) . PHP_EOL;"
// Reemplazá el valor de abajo por el resultado.
// ============================================================
define('ADMIN_USER', 'admin');
define('ADMIN_HASH', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

// ============================================================
// RUTAS
// ============================================================
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('PHOTO_FILE', 'foto_personal.jpg');
