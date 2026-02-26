<?php
// =============================================
// PLAYTIME LAUNCHER — Backend formulario
// guardar_consulta.php
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

require_once 'db_config.php';

// --- Leer y sanitizar datos ---
$tipo        = trim($_POST['tipo']        ?? '');
$titulo      = trim($_POST['titulo']      ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$email       = trim($_POST['email']       ?? '');

// --- Validaciones ---
$errores = [];

if (!in_array($tipo, ['bug', 'propuesta'])) {
    $errores[] = 'El tipo de consulta no es válido.';
}

if (mb_strlen($titulo) < 5 || mb_strlen($titulo) > 150) {
    $errores[] = 'El título debe tener entre 5 y 150 caracteres.';
}

if (mb_strlen($descripcion) < 15) {
    $errores[] = 'La descripción es demasiado corta (mínimo 15 caracteres).';
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email ingresado no es válido.';
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'errores' => $errores]);
    exit;
}

// --- Guardar en BD ---
try {
    $pdo = conectar_db();

    $stmt = $pdo->prepare(
        "INSERT INTO consultas (tipo, titulo, descripcion, email)
         VALUES (:tipo, :titulo, :descripcion, :email)"
    );

    $stmt->execute([
        ':tipo'        => $tipo,
        ':titulo'      => $titulo,
        ':descripcion' => $descripcion,
        ':email'       => $email !== '' ? $email : null,
    ]);

    echo json_encode(['ok' => true, 'mensaje' => '¡Consulta enviada correctamente!']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno del servidor. Intenta más tarde.']);
}
?>
