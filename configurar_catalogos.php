<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

try {
    $conn = getConnection();

    $tab = $_GET['tab'] ?? 'carrera';
    $validTabs = ['carrera', 'turno', 'grado'];
    if (!in_array($tab, $validTabs)) $tab = 'carrera';

    // ============================
    // ACCIONES: ACTIVAR / DESACTIVAR
    // ============================
    if (isset($_GET['accion'], $_GET['id'])) {
        $accion = $_GET['accion'];
        $id = intval($_GET['id']);

        if ($tab === 'carrera') {
            if ($accion === 'desactivar') {
                $stmt = $conn->prepare("UPDATE carrera SET activo=0 WHERE id_carrera=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Carrera desactivada";
                $tipo_mensaje = "exito";
            } elseif ($accion === 'activar') {
                $stmt = $conn->prepare("UPDATE carrera SET activo=1 WHERE id_carrera=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Carrera activada";
                $tipo_mensaje = "exito";
            }
        }

        if ($tab === 'turno') {
            if ($accion === 'desactivar') {
                $stmt = $conn->prepare("UPDATE turno SET activo=0 WHERE id_turno=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Turno desactivado";
                $tipo_mensaje = "exito";
            } elseif ($accion === 'activar') {
                $stmt = $conn->prepare("UPDATE turno SET activo=1 WHERE id_turno=?");
                $stmt->bind_param("i", $id);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Turno activado";
                $tipo_mensaje = "exito";
            }
        }

        if ($tab === 'grado') {
            if ($accion === 'desactivar') {
                $stmt = $conn->prepare("UPDATE grado SET activo=0 WHERE id_grado=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Grado desactivado";
                $tipo_mensaje = "exito";
            } elseif ($accion === 'activar') {
                $stmt = $conn->prepare("UPDATE grado SET activo=1 WHERE id_grado=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Grado activado";
                $tipo_mensaje = "exito";
            }
        }
    }

    // ============================
    // REGISTRO (INSERT)
    // ============================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if ($tab === 'carrera') {
            $nombre = trim($_POST['nombre'] ?? '');
            $codigo = trim($_POST['codigo'] ?? '');

            if ($nombre !== '' && $codigo !== '') {
                $stmt = $conn->prepare("INSERT INTO carrera (nombre, codigo, activo) VALUES (?, ?, 1)");
                $stmt->bind_param("ss", $nombre, $codigo);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Carrera registrada";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "Completa nombre y código";
                $tipo_mensaje = "error";
            }
        }

        if ($tab === 'turno') {
            $nombre = trim($_POST['nombre'] ?? '');
            $sigla = trim($_POST['sigla'] ?? '');

            if ($nombre !== '' && $sigla !== '') {
                $stmt = $conn->prepare("INSERT INTO turno (nombre, sigla, activo) VALUES (?, ?, 1)");
                $stmt->bind_param("ss", $nombre, $sigla);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Turno registrado";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "Completa nombre y sigla";
                $tipo_mensaje = "error";
            }
        }

        if ($tab === 'grado') {
            $numero = intval($_POST['numero'] ?? 0);

            if ($numero > 0) {
                $stmt = $conn->prepare("INSERT INTO grado (numero, activo) VALUES (?, 1)");
                $stmt->bind_param("i", $numero);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Grado registrado";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "El grado debe ser un número válido";
                $tipo_mensaje = "error";
            }
        }
    }

    // ============================
    // LISTADOS
    // ============================
    $carreras = $conn->query("
        SELECT 
            c.*,
            (SELECT COUNT(*) FROM grupo g WHERE g.id_carrera = c.id_carrera) AS total_grupos
        FROM carrera c
        ORDER BY c.nombre
    ");

    $turnos = $conn->query("
        SELECT 
            t.*,
            (SELECT COUNT(*) FROM grupo g WHERE g.id_turno = t.id_turno) AS total_grupos
        FROM turno t
        ORDER BY t.nombre
    ");

    $grados = $conn->query("
        SELECT 
            gr.*,
            (SELECT COUNT(*) FROM grupo g WHERE g.id_grado = gr.id_grado) AS total_grupos
        FROM grado gr
        ORDER BY gr.numero
    ");

    $conn->close();

} catch (Throwable $e) {
    $mensaje = "Error interno: " . $e->getMessage();
    $tipo_mensaje = "error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configurar Catálogos</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
*{margin:0;padding:0;box-sizing:border-box}

body{
    font-family:'Inter',sans-serif;
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

.wrap{max-width:1200px;margin:auto;position:relative;z-index:1}

.header{
    display:flex;justify-content:space-between;align-items:center;
    flex-wrap:wrap;gap:14px;margin-bottom:18px;
}
.header h1{
    color:#fff;font-size:22px;font-weight:800;
    letter-spacing:-.4px;
}

.btn{
    padding:12px 18px;border-radius:12px;
    text-decoration:none;font-weight:800;
    color:#fff;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    transition:.25s;
    display:inline-flex;align-items:center;gap:8px;
}
.btn:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(99,102,241,.35)}

.card{
    background:rgba(15,15,30,.55);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.10);
    border-radius:18px;
    padding:25px;
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

.tabs{
    display:flex;gap:10px;flex-wrap:wrap;
    margin:14px 0 18px;
}
.tab{
    padding:10px 16px;border-radius:12px;
    text-decoration:none;font-weight:900;font-size:13px;
    color:rgba(255,255,255,.75);
    border:1px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.06);
    transition:.2s;
}
.tab:hover{transform:translateY(-2px);border-color:rgba(99,102,241,.45)}
.tab.active{
    color:#fff;
    border-color:rgba(99,102,241,.55);
    background:linear-gradient(135deg, rgba(99,102,241,.30), rgba(139,92,246,.22));
}

form{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:14px;
    padding:16px;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.05);
    margin-bottom:18px;
}
label{color:#c7d2fe;font-size:13px;font-weight:900;margin-bottom:6px;display:block}
input{
    width:100%;
    padding:12px;border-radius:12px;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.06);
    color:#fff;
    outline:none;
}
input::placeholder{color:rgba(255,255,255,.35)}
input:focus{
    border-color:rgba(99,102,241,.55);
    box-shadow:0 0 0 4px rgba(99,102,241,.18);
}
.submit{
    grid-column:1/-1;
    padding:14px;border:none;border-radius:14px;
    font-weight:900;font-size:14px;
    cursor:pointer;color:#fff;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    transition:.25s;
}
.submit:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(99,102,241,.35)}

table{width:100%;border-collapse:collapse;margin-top:12px}
th,td{padding:12px;border-bottom:1px solid rgba(255,255,255,.10);text-align:left}
th{color:#fff;font-size:13px}
td{color:#c7d2fe;font-size:13px}

.badge{
    display:inline-block;padding:6px 12px;border-radius:999px;
    background:rgba(99,102,241,.2);
    border:1px solid rgba(99,102,241,.35);
    color:#e0e7ff;font-weight:900;font-size:12px;
}
.action{
    padding:8px 12px;border-radius:12px;
    text-decoration:none;font-weight:900;font-size:12px;
    display:inline-block;
    border:1px solid rgba(255,255,255,.12);
    transition:.2s;
}
.red{background:rgba(239,68,68,.14);color:#fecaca;border-color:rgba(239,68,68,.35)}
.green{background:rgba(34,197,94,.14);color:#bbf7d0;border-color:rgba(34,197,94,.35)}
.action:hover{transform:translateY(-2px)}
.muted{color:rgba(255,255,255,.45);font-size:12px;margin-top:8px}
</style>
</head>

<body>
<div class="wrap">

    <div class="header">
        <h1>⚙️ Configuración de Catálogos</h1>
        <a href="index.php" class="btn">🏠 Volver al Inicio</a>
    </div>

    <div class="card">

        <div class="tabs">
            <a class="tab <?php echo $tab==='carrera'?'active':''; ?>" href="?tab=carrera">Carreras</a>
            <a class="tab <?php echo $tab==='turno'?'active':''; ?>" href="?tab=turno">Turnos</a>
            <a class="tab <?php echo $tab==='grado'?'active':''; ?>" href="?tab=grado">Grados</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <?php if ($tab === 'carrera'): ?>
            <form method="POST">
                <div>
                    <label>Nombre</label>
                    <input name="nombre" placeholder="Ej: Sistemas" required>
                </div>
                <div>
                    <label>Código</label>
                    <input name="codigo" placeholder="Ej: ISC" required>
                </div>
                <button class="submit" type="submit">Guardar Carrera</button>
                <div class="muted">Solo carreras activas aparecen al registrar grupos.</div>
            </form>

            <table>
                <thead><tr><th>Nombre</th><th>Código</th><th>Grupos</th><th>Estado</th><th>Acción</th></tr></thead>
                <tbody>
                <?php while($c = $carreras->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                        <td><span class="badge"><?php echo htmlspecialchars($c['codigo']); ?></span></td>
                        <td><?php echo (int)$c['total_grupos']; ?></td>
                        <td><?php echo $c['activo'] ? 'Activa' : 'Inactiva'; ?></td>
                        <td>
                            <?php if ($c['activo']): ?>
                                <a class="action red" href="?tab=carrera&accion=desactivar&id=<?php echo $c['id_carrera']; ?>">Desactivar</a>
                            <?php else: ?>
                                <a class="action green" href="?tab=carrera&accion=activar&id=<?php echo $c['id_carrera']; ?>">Activar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        <?php elseif ($tab === 'turno'): ?>
            <form method="POST">
                <div>
                    <label>Nombre</label>
                    <input name="nombre" placeholder="Ej: Vespertino" required>
                </div>
                <div>
                    <label>Sigla</label>
                    <input name="sigla" placeholder="Ej: V" required>
                </div>
                <button class="submit" type="submit">Guardar Turno</button>
            </form>

            <table>
                <thead><tr><th>Nombre</th><th>Sigla</th><th>Grupos</th><th>Estado</th><th>Acción</th></tr></thead>
                <tbody>
                <?php while($t = $turnos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['nombre']); ?></td>
                        <td><span class="badge"><?php echo htmlspecialchars($t['sigla']); ?></span></td>
                        <td><?php echo (int)$t['total_grupos']; ?></td>
                        <td><?php echo $t['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                        <td>
                            <?php if ($t['activo']): ?>
                                <a class="action red" href="?tab=turno&accion=desactivar&id=<?php echo $t['id_turno']; ?>">Desactivar</a>
                            <?php else: ?>
                                <a class="action green" href="?tab=turno&accion=activar&id=<?php echo $t['id_turno']; ?>">Activar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <form method="POST">
                <div>
                    <label>Número</label>
                    <input name="numero" type="number" min="1" max="20" placeholder="Ej: 8" required>
                </div>
                <button class="submit" type="submit">Guardar Grado</button>
            </form>

            <table>
                <thead><tr><th>Grado</th><th>Grupos</th><th>Estado</th><th>Acción</th></tr></thead>
                <tbody>
                <?php while($gr = $grados->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge"><?php echo htmlspecialchars($gr['numero']); ?></span></td>
                        <td><?php echo (int)$gr['total_grupos']; ?></td>
                        <td><?php echo $gr['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                        <td>
                            <?php if ($gr['activo']): ?>
                                <a class="action red" href="?tab=grado&accion=desactivar&id=<?php echo $gr['id_grado']; ?>">Desactivar</a>
                            <?php else: ?>
                                <a class="action green" href="?tab=grado&accion=activar&id=<?php echo $gr['id_grado']; ?>">Activar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
