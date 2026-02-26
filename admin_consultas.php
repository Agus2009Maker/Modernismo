<?php
// =============================================
// PLAYTIME LAUNCHER — Panel de Administración
// admin_consultas.php
// ⚠ Protegé este archivo con .htpasswd en producción
// =============================================

require_once 'db_config.php';

// ---- Acción: cambiar estado ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    try {
        $pdo = conectar_db();
        if ($_POST['accion'] === 'cambiar_estado') {
            $id     = (int)$_POST['id'];
            $estado = $_POST['estado'];
            if (in_array($estado, ['pendiente', 'revisado', 'resuelto'])) {
                $pdo->prepare("UPDATE consultas SET estado = ? WHERE id = ?")->execute([$estado, $id]);
            }
        }
        if ($_POST['accion'] === 'eliminar') {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM consultas WHERE id = ?")->execute([$id]);
        }
    } catch (PDOException $e) {}
    header('Location: admin_consultas.php' . ($_GET ? '?' . http_build_query($_GET) : ''));
    exit;
}

// ---- Filtros ----
$filtro_tipo   = $_GET['tipo']   ?? 'todos';
$filtro_estado = $_GET['estado'] ?? 'todos';
$busqueda      = trim($_GET['q'] ?? '');
$pagina        = max(1, (int)($_GET['p'] ?? 1));
$por_pagina    = 15;
$offset        = ($pagina - 1) * $por_pagina;

// ---- Consulta ----
try {
    $pdo = conectar_db();

    $where = [];
    $params = [];

    if ($filtro_tipo !== 'todos') {
        $where[] = 'tipo = ?';
        $params[] = $filtro_tipo;
    }
    if ($filtro_estado !== 'todos') {
        $where[] = 'estado = ?';
        $params[] = $filtro_estado;
    }
    if ($busqueda !== '') {
        $where[] = '(titulo LIKE ? OR descripcion LIKE ? OR email LIKE ?)';
        $params[] = "%$busqueda%";
        $params[] = "%$busqueda%";
        $params[] = "%$busqueda%";
    }

    $sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total
    $stmt_total = $pdo->prepare("SELECT COUNT(*) FROM consultas $sql_where");
    $stmt_total->execute($params);
    $total = (int)$stmt_total->fetchColumn();
    $total_paginas = max(1, ceil($total / $por_pagina));

    // Filas
    $stmt = $pdo->prepare(
        "SELECT * FROM consultas $sql_where
         ORDER BY fecha DESC
         LIMIT $por_pagina OFFSET $offset"
    );
    $stmt->execute($params);
    $consultas = $stmt->fetchAll();

    // Stats globales
    $stats = $pdo->query(
        "SELECT
            COUNT(*) as total,
            SUM(tipo='bug') as bugs,
            SUM(tipo='propuesta') as propuestas,
            SUM(estado='pendiente') as pendientes,
            SUM(estado='revisado') as revisados,
            SUM(estado='resuelto') as resueltos
         FROM consultas"
    )->fetch();

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ---- Helpers ----
function badge_tipo($tipo) {
    $map = [
        'bug'       => ['🐛', '#cc3300', 'BUG'],
        'propuesta' => ['💡', '#9b00d9', 'PROPUESTA'],
    ];
    $d = $map[$tipo] ?? ['❓', '#888', strtoupper($tipo)];
    return "<span style='color:{$d[1]};font-size:0.75rem;font-weight:bold;letter-spacing:0.08em;'>{$d[0]} {$d[2]}</span>";
}

function badge_estado($estado) {
    $map = [
        'pendiente' => ['#ffcc00', '⏳ PENDIENTE'],
        'revisado'  => ['#4499ff', '👁 REVISADO'],
        'resuelto'  => ['#00cc66', '✓ RESUELTO'],
    ];
    $d = $map[$estado] ?? ['#888', strtoupper($estado)];
    return "<span style='color:{$d[0]};font-size:0.75rem;font-weight:bold;'>{$d[1]}</span>";
}

function url_filtro($extra = []) {
    $base = array_merge([
        'tipo'   => $_GET['tipo']   ?? 'todos',
        'estado' => $_GET['estado'] ?? 'todos',
        'q'      => $_GET['q']      ?? '',
        'p'      => 1,
    ], $extra);
    return '?' . http_build_query(array_filter($base, fn($v) => $v !== '' && $v !== 'todos' && $v !== '1'));
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Consultas | Playtime Launcher</title>
    <link rel="stylesheet" href="Web.css">
    <link rel="icon" href="./Assets/icon.png">
    <style>
        body { font-size: 0.9rem; }

        .admin-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px 60px;
        }

        .admin-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-header h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            color: var(--accent);
            text-shadow: var(--neon-main);
            letter-spacing: 0.08em;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 14px;
            text-align: center;
        }

        .stat-card .num {
            font-family: var(--font-display);
            font-size: 2rem;
            line-height: 1;
            color: var(--accent);
            text-shadow: var(--neon-main);
        }

        .stat-card .lbl {
            font-size: 0.68rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Filtros */
        .filtros {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            align-items: center;
        }

        .filtros a {
            padding: 5px 12px;
            border: 1px solid var(--border-color);
            border-radius: 3px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            transition: all 0.2s;
        }

        .filtros a:hover, .filtros a.activo {
            border-color: var(--accent);
            color: var(--accent);
            text-shadow: var(--neon-main);
        }

        .filtros form {
            display: flex;
            gap: 8px;
            margin-left: auto;
        }

        .filtros input[type=text] {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 3px;
            padding: 5px 12px;
            color: var(--text-color);
            font-family: var(--font-mono);
            font-size: 0.82rem;
            outline: none;
            width: 220px;
            transition: border-color 0.2s;
        }

        .filtros input[type=text]:focus {
            border-color: var(--accent);
            box-shadow: var(--neon-main);
        }

        .filtros button {
            padding: 5px 14px;
            background: var(--accent);
            color: #000;
            border: none;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            font-weight: bold;
            letter-spacing: 0.08em;
            cursor: pointer;
            transition: filter 0.2s;
        }

        .filtros button:hover { filter: brightness(0.85); }

        /* Tabla */
        .tabla-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
        }

        th {
            text-align: left;
            padding: 10px 14px;
            border-bottom: 1px solid var(--accent);
            color: var(--accent);
            text-shadow: var(--neon-main);
            font-family: var(--font-mono);
            font-size: 0.72rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-muted);
            vertical-align: top;
        }

        tr:hover td { background: var(--card-bg); }

        td.titulo-col {
            color: var(--text-color);
            max-width: 260px;
        }

        td.desc-col {
            max-width: 300px;
            color: var(--text-muted);
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .desc-preview {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Formulario inline de estado */
        .form-estado {
            display: flex;
            gap: 6px;
            align-items: center;
            white-space: nowrap;
        }

        .form-estado select {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            font-family: var(--font-mono);
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 3px;
            outline: none;
            cursor: pointer;
        }

        .form-estado button {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 4px 10px;
            font-family: var(--font-mono);
            font-size: 0.7rem;
            font-weight: bold;
            letter-spacing: 0.06em;
            cursor: pointer;
            border-radius: 2px;
        }

        .btn-eliminar {
            background: transparent;
            border: 1px solid rgba(200,0,0,0.4);
            color: #cc3300;
            padding: 4px 10px;
            font-family: var(--font-mono);
            font-size: 0.7rem;
            font-weight: bold;
            cursor: pointer;
            border-radius: 2px;
            transition: all 0.2s;
            margin-top: 6px;
            display: block;
        }

        .btn-eliminar:hover {
            background: rgba(200,0,0,0.15);
            border-color: #cc3300;
        }

        /* Paginación */
        .paginacion {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .paginacion a, .paginacion span {
            padding: 6px 14px;
            border: 1px solid var(--border-color);
            border-radius: 3px;
            color: var(--text-muted);
            text-decoration: none;
            font-family: var(--font-mono);
            font-size: 0.78rem;
            transition: all 0.2s;
        }

        .paginacion a:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .paginacion span.actual {
            border-color: var(--accent);
            color: var(--accent);
            text-shadow: var(--neon-main);
        }

        .sin-resultados {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <img src="./Assets/Logo/Mob_Entertainment.png" alt="Mob Entertainment" class="nav-logo">
        <ul>
            <li><a href="index.html">← Inicio</a></li>
            <li><a href="consultas.html">Ver Formulario</a></li>
        </ul>
        <button id="theme-toggle" class="btn-neon">Modo Diurno ☀️</button>
    </nav>

    <div class="admin-wrap fade-in">

        <div class="admin-header">
            <h1>⚙ Panel Admin — Consultas</h1>
            <a href="consultas.html" class="btn-neon" style="text-decoration:none; font-size:0.75rem;">
                VER FORMULARIO PÚBLICO
            </a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="num"><?= $stats['total'] ?></div>
                <div class="lbl">Total</div>
            </div>
            <div class="stat-card">
                <div class="num" style="color:#cc3300; text-shadow:var(--neon-red);"><?= $stats['bugs'] ?></div>
                <div class="lbl">🐛 Bugs</div>
            </div>
            <div class="stat-card">
                <div class="num" style="color:#9b00d9;"><?= $stats['propuestas'] ?></div>
                <div class="lbl">💡 Propuestas</div>
            </div>
            <div class="stat-card">
                <div class="num" style="color:#ffcc00;"><?= $stats['pendientes'] ?></div>
                <div class="lbl">⏳ Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="num" style="color:#4499ff;"><?= $stats['revisados'] ?></div>
                <div class="lbl">👁 Revisados</div>
            </div>
            <div class="stat-card">
                <div class="num" style="color:#00cc66;"><?= $stats['resueltos'] ?></div>
                <div class="lbl">✓ Resueltos</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros">
            <a href="<?= url_filtro(['tipo'=>'todos']) ?>" class="<?= $filtro_tipo==='todos'?'activo':'' ?>">Todos</a>
            <a href="<?= url_filtro(['tipo'=>'bug']) ?>" class="<?= $filtro_tipo==='bug'?'activo':'' ?>">🐛 Bugs</a>
            <a href="<?= url_filtro(['tipo'=>'propuesta']) ?>" class="<?= $filtro_tipo==='propuesta'?'activo':'' ?>">💡 Propuestas</a>
            &nbsp;|&nbsp;
            <a href="<?= url_filtro(['estado'=>'todos']) ?>" class="<?= $filtro_estado==='todos'?'activo':'' ?>">Todos</a>
            <a href="<?= url_filtro(['estado'=>'pendiente']) ?>" class="<?= $filtro_estado==='pendiente'?'activo':'' ?>">⏳ Pendiente</a>
            <a href="<?= url_filtro(['estado'=>'revisado']) ?>" class="<?= $filtro_estado==='revisado'?'activo':'' ?>">👁 Revisado</a>
            <a href="<?= url_filtro(['estado'=>'resuelto']) ?>" class="<?= $filtro_estado==='resuelto'?'activo':'' ?>">✓ Resuelto</a>

            <form method="GET" action="">
                <input type="hidden" name="tipo" value="<?= htmlspecialchars($filtro_tipo) ?>">
                <input type="hidden" name="estado" value="<?= htmlspecialchars($filtro_estado) ?>">
                <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar...">
                <button type="submit">BUSCAR</button>
            </form>
        </div>

        <p style="color:var(--text-muted); font-size:0.78rem; margin-bottom:14px;">
            <?= $total ?> resultado<?= $total !== 1 ? 's' : '' ?> encontrado<?= $total !== 1 ? 's' : '' ?>
            — Página <?= $pagina ?> de <?= $total_paginas ?>
        </p>

        <!-- Tabla -->
        <?php if (empty($consultas)): ?>
            <div class="sin-resultados">
                <p>No hay consultas que coincidan con los filtros seleccionados.</p>
            </div>
        <?php else: ?>
        <div class="tabla-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Email</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($consultas as $c): ?>
                <tr>
                    <td style="color:var(--text-muted); font-size:0.78rem;"><?= $c['id'] ?></td>
                    <td><?= badge_tipo($c['tipo']) ?></td>
                    <td class="titulo-col"><?= htmlspecialchars($c['titulo']) ?></td>
                    <td class="desc-col">
                        <div class="desc-preview"><?= nl2br(htmlspecialchars($c['descripcion'])) ?></div>
                    </td>
                    <td style="font-size:0.8rem;">
                        <?php if ($c['email']): ?>
                            <a href="mailto:<?= htmlspecialchars($c['email']) ?>"
                               style="color:var(--accent); text-decoration:none; font-size:0.8rem;">
                               <?= htmlspecialchars($c['email']) ?>
                            </a>
                        <?php else: ?>
                            <span style="opacity:0.4; font-size:0.78rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap; font-size:0.78rem;">
                        <?= date('d/m/Y', strtotime($c['fecha'])) ?><br>
                        <span style="opacity:0.5;"><?= date('H:i', strtotime($c['fecha'])) ?></span>
                    </td>
                    <td><?= badge_estado($c['estado']) ?></td>
                    <td>
                        <!-- Cambiar estado -->
                        <form method="POST" class="form-estado">
                            <input type="hidden" name="accion" value="cambiar_estado">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <select name="estado">
                                <option value="pendiente" <?= $c['estado']==='pendiente'?'selected':'' ?>>⏳ Pendiente</option>
                                <option value="revisado"  <?= $c['estado']==='revisado'?'selected':'' ?>>👁 Revisado</option>
                                <option value="resuelto"  <?= $c['estado']==='resuelto'?'selected':'' ?>>✓ Resuelto</option>
                            </select>
                            <button type="submit">OK</button>
                        </form>
                        <!-- Eliminar -->
                        <form method="POST" onsubmit="return confirm('¿Eliminar esta consulta? Esta acción no se puede deshacer.')">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn-eliminar">🗑 ELIMINAR</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>

        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
        <div class="paginacion">
            <?php if ($pagina > 1): ?>
                <a href="<?= url_filtro(['p' => $pagina - 1]) ?>">← Anterior</a>
            <?php endif; ?>

            <?php for ($i = max(1, $pagina-2); $i <= min($total_paginas, $pagina+2); $i++): ?>
                <?php if ($i === $pagina): ?>
                    <span class="actual"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= url_filtro(['p' => $i]) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pagina < $total_paginas): ?>
                <a href="<?= url_filtro(['p' => $pagina + 1]) ?>">Siguiente →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>

    <script src="Web.js"></script>
</body>
</html>
