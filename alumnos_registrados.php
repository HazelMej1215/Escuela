<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

try {
    // Eliminar alumno
    if (isset($_GET['eliminar'])) {
        $id = intval($_GET['eliminar']);
        $conn = getConnection();

        $stmt = $conn->prepare("DELETE FROM alumno WHERE id_alumno = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $mensaje = "Alumno eliminado exitosamente";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al eliminar el alumno";
            $tipo_mensaje = "error";
        }

        $stmt->close();
        $conn->close();
    }

    // Obtener alumnos con joins (grupo + carrera + turno)
    $conn = getConnection();

    $sql = "
SELECT
    a.id_alumno,
    a.nombre,
    a.apellido_p,
    a.apellido_m,
    a.fecha_registro,
    g.codigo_grupo,
    c.nombre AS carrera,
    t.nombre AS turno,
    gr.numero AS grado
FROM alumno a
INNER JOIN grupo g ON a.id_grupo = g.id_grupo
INNER JOIN carrera c ON g.id_carrera = c.id_carrera
INNER JOIN turno t ON g.id_turno = t.id_turno
INNER JOIN grado gr ON g.id_grado = gr.id_grado
WHERE c.activo = 1
  AND t.activo = 1
  AND gr.activo = 1
ORDER BY a.fecha_registro DESC
";


    $alumnos = $conn->query($sql);

    // Estadísticas
    $total_alumnos = $alumnos->num_rows;

    $stat = $conn->query("SELECT COUNT(DISTINCT id_grupo) AS total FROM alumno")->fetch_assoc();
    $total_grupos = $stat['total'] ?? 0;

    $conn->close();

} catch (Throwable $e) {
    $mensaje = "Error interno: " . $e->getMessage();
    $tipo_mensaje = "error";
    $total_alumnos = 0;
    $total_grupos = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos Registrados</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        *{margin:0;padding:0;box-sizing:border-box}

        body{
            font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
            background:#0f0f1e;
            min-height:100vh;
            padding:25px;
            position:relative;
        }
        body::before{
            content:'';
            position:fixed;inset:0;
            background:
                radial-gradient(circle at 20% 50%, rgba(120,119,198,.30), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(99,102,241,.20), transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(168,85,247,.15), transparent 50%);
            z-index:0;
        }

        .wrap{max-width:1400px;margin:auto;position:relative;z-index:1}

        .header{
            display:flex;justify-content:space-between;align-items:center;
            flex-wrap:wrap;gap:14px;margin-bottom:18px;
        }
        .title{
            display:flex;align-items:center;gap:14px;
        }
        .title .icon{
            width:44px;height:44px;border-radius:12px;
            background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 12px 30px rgba(99,102,241,.25);
            font-size:22px;
        }
        .title h1{
            color:#fff;font-weight:800;font-size:22px;letter-spacing:-.4px;
        }
        .title p{
            color:rgba(255,255,255,.55);
            font-size:12px;margin-top:2px;
        }

        .btn{
            padding:12px 18px;border-radius:12px;
            text-decoration:none;font-weight:800;
            color:#fff;
            background:linear-gradient(135deg,#6366f1,#8b5cf6);
            transition:.25s;
            display:inline-flex;align-items:center;gap:8px;
            border:1px solid rgba(255,255,255,.10);
            backdrop-filter: blur(10px);
        }
        .btn:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(99,102,241,.35)}

        .card{
            background:rgba(15,15,30,.55);
            backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,.10);
            border-radius:18px;
            padding:22px;
            box-shadow:0 20px 60px rgba(0,0,0,.35);
            animation:fadeInUp .45s ease both;
        }
        @keyframes fadeInUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}

        .mensaje{
            padding:12px 14px;border-radius:12px;
            margin-bottom:16px;font-weight:800;font-size:13px;
            border:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.06);
        }
        .exito{border-color:rgba(34,197,94,.35);background:rgba(34,197,94,.12);color:#bbf7d0}
        .error{border-color:rgba(239,68,68,.35);background:rgba(239,68,68,.12);color:#fecaca}

        .stats{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
            gap:16px;
            margin-bottom:18px;
        }
        .stat{
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.10);
            border-radius:16px;
            padding:18px;
            animation:fadeInUp .55s ease both;
        }
        .stat:nth-child(1){animation-delay:.05s}
        .stat:nth-child(2){animation-delay:.12s}
        .stat .num{
            font-size:34px;font-weight:900;
            background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text;
            letter-spacing:-1px;
        }
        .stat .label{color:rgba(255,255,255,.85);font-weight:700;font-size:13px;margin-top:2px}
        .stat .sub{color:rgba(255,255,255,.45);font-size:12px;margin-top:6px}

        .table-wrap{
            overflow:auto;
            border-radius:16px;
            border:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.03);
        }
        table{width:100%;border-collapse:collapse;min-width:900px}
        th,td{padding:14px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left}
        th{color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:.7px}
        td{color:#c7d2fe;font-size:13px}
        tr:hover td{background:rgba(255,255,255,.04)}

        .badge{
            display:inline-block;padding:6px 12px;border-radius:999px;
            background:rgba(99,102,241,.2);
            border:1px solid rgba(99,102,241,.35);
            color:#e0e7ff;font-weight:900;font-size:12px;
        }

        .acciones{display:flex;gap:10px;flex-wrap:wrap}
        .action{
            padding:9px 12px;border-radius:12px;
            text-decoration:none;font-weight:900;font-size:12px;
            display:inline-block;
            border:1px solid rgba(255,255,255,.12);
            transition:.2s;
        }
        .edit{background:rgba(34,197,94,.14);color:#bbf7d0;border-color:rgba(34,197,94,.35)}
        .del{background:rgba(239,68,68,.14);color:#fecaca;border-color:rgba(239,68,68,.35)}
        .action:hover{transform:translateY(-2px)}

        .empty{
            text-align:center;
            padding:50px 20px;
            color:rgba(255,255,255,.55);
        }
        .empty .icon{
            font-size:56px;opacity:.6;margin-bottom:10px
        }
        .empty h2{color:#fff;font-size:18px;font-weight:800;margin-bottom:8px}
        .empty p{color:rgba(255,255,255,.55);font-size:13px}
    </style>

    <script>
        function confirmarEliminacion(nombre) {
            return confirm('¿Está seguro de que desea eliminar al alumno ' + nombre + '?');
        }
    </script>
</head>

<body>
<div class="wrap">

    <div class="header">
        <div class="title">
            <div class="icon">📋</div>
            <div>
                <h1>Alumnos Registrados</h1>
                <p>Listado con Carrera, Turno y Grupo</p>
            </div>
        </div>

        <a class="btn" href="index.php">🏠 Volver al Inicio</a>
    </div>

    <div class="card">

        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if ($total_alumnos > 0): ?>

            <div class="stats">
                <div class="stat">
                    <div class="num"><?php echo (int)$total_alumnos; ?></div>
                    <div class="label">Total Alumnos</div>
                    <div class="sub">Registrados en el sistema</div>
                </div>
                <div class="stat">
                    <div class="num"><?php echo (int)$total_grupos; ?></div>
                    <div class="label">Grupos con Alumnos</div>
                    <div class="sub">Grupos distintos usados</div>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Grupo</th>
                        <th>Carrera</th>
                        <th>Turno</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($alumno = $alumnos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo (int)$alumno['id_alumno']; ?></td>
                            <td>
                                <strong style="color:#fff;">
                                    <?php echo htmlspecialchars($alumno['nombre'].' '.$alumno['apellido_p'].' '.$alumno['apellido_m']); ?>
                                </strong>
                            </td>
                            <td><span class="badge"><?php echo htmlspecialchars($alumno['codigo_grupo']); ?></span></td>
                            <td><?php echo htmlspecialchars($alumno['carrera']); ?></td>
                            <td><?php echo htmlspecialchars($alumno['turno']); ?></td>
                            <td>
                                <div class="acciones">
                                    <a class="action edit" href="registrar_alumno.php?editar=<?php echo (int)$alumno['id_alumno']; ?>">✏️ Editar</a>
                                    <a class="action del"
                                       href="?eliminar=<?php echo (int)$alumno['id_alumno']; ?>"
                                       onclick="return confirmarEliminacion('<?php echo htmlspecialchars($alumno['nombre'].' '.$alumno['apellido_p']); ?>')">
                                        🗑️ Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="empty">
                <div class="icon">📭</div>
                <h2>No hay alumnos registrados</h2>
                <p>Registra un alumno para que aparezca aquí.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
