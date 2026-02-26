<?php
// =============================================
// PLAYTIME LAUNCHER — Configuración de BD
// Completá estos datos con los tuyos
// =============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'playtime_db');
define('DB_USER', 'Agus');
define('DB_PASS', 'santisoria');
define('DB_CHARSET', 'utf8mb4');

function conectar_db(): PDO {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $opciones);
}
?>
