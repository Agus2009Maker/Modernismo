<?php
// =============================================
// PLAYTIME LAUNCHER — Estadísticas públicas
// stats_consultas.php
// =============================================

header('Content-Type: application/json');

require_once 'db_config.php';

try {
    $pdo = conectar_db();

    $stmt = $pdo->query(
        "SELECT tipo, COUNT(*) as total
         FROM consultas
         GROUP BY tipo"
    );

    $bugs = 0;
    $propuestas = 0;

    foreach ($stmt->fetchAll() as $row) {
        if ($row['tipo'] === 'bug')       $bugs      = (int)$row['total'];
        if ($row['tipo'] === 'propuesta') $propuestas = (int)$row['total'];
    }

    echo json_encode([
        'ok'         => true,
        'bugs'       => $bugs,
        'propuestas' => $propuestas,
        'total'      => $bugs + $propuestas,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false]);
}
?>
