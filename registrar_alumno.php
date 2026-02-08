<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';
$alumno_editar = null;

try {
    $conn = getConnection();

    // --- CARGAR GRUPOS ---
    // Tabla: grupo (normalizada)
    $grupos = $conn->query("
    SELECT 
        g.id_grupo,
        g.codigo_grupo,
        c.nombre AS carrera,
        t.nombre AS turno,
        gr.numero AS grado
    FROM grupo g
    INNER JOIN carrera c ON g.id_carrera = c.id_carrera
    INNER JOIN turno t ON g.id_turno = t.id_turno
    INNER JOIN grado gr ON g.id_grado = gr.id_grado
    WHERE c.activo = 1
      AND t.activo = 1
      AND gr.activo = 1
    ORDER BY c.nombre, gr.numero, t.nombre, g.codigo_grupo
");


    // --- EDITAR ALUMNO ---
    if (isset($_GET['editar'])) {
        $id = intval($_GET['editar']);
        $stmt = $conn->prepare("SELECT * FROM alumno WHERE id_alumno = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $alumno_editar = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    // --- GUARDAR / ACTUALIZAR ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
        $apellido_materno = trim($_POST['apellido_materno'] ?? '');
        $id_grupo = intval($_POST['id_grupo'] ?? 0);
        $id_alumno = intval($_POST['id_alumno'] ?? 0);

        if ($nombre !== '' && $apellido_paterno !== '' && $apellido_materno !== '' && $id_grupo > 0) {

            if ($id_alumno > 0) {
                $stmt = $conn->prepare("
                    UPDATE alumno
                    SET nombre=?, apellido_paterno=?, apellido_materno=?, id_grupo=?
                    WHERE id_alumno=?
                ");
                $stmt->bind_param("sssii", $nombre, $apellido_paterno, $apellido_materno, $id_grupo, $id_alumno);
                $stmt->execute();
                $stmt->close();

                $mensaje = "Alumno actualizado correctamente";
                $tipo_mensaje = "exito";
                $alumno_editar = null;

            } else {
                $stmt = $conn->prepare("
                    INSERT INTO alumno (nombre, apellido_paterno, apellido_materno, id_grupo)
                    VALUES (?,?,?,?)
                ");
                $stmt->bind_param("sssi", $nombre, $apellido_paterno, $apellido_materno, $id_grupo);
                $stmt->execute();
                $stmt->close();

                $mensaje = "Alumno registrado correctamente";
                $tipo_mensaje = "exito";
            }

        } else {
            $mensaje = "Completa todos los campos";
            $tipo_mensaje = "error";
        }
    }

    $conn->close();

} catch (Throwable $e) {
    // Esto evita la “pantalla en blanco”
    $mensaje = "Error interno: " . $e->getMessage();
    $tipo_mensaje = "error";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Alumno</title>

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

.wrap{max-width:1100px;margin:auto;position:relative;z-index:1}

.header{
    display:flex;justify-content:space-between;align-items:center;
    flex-wrap:wrap;gap:14px;margin-bottom:18px;
}
.header h1{color:#fff;font-size:22px;font-weight:800}

.btn{
    padding:12px 18px;
    border-radius:12px;
    text-decoration:none;
    font-weight:800;
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

.form{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:16px;
}
label{color:#c7d2fe;font-size:13px;font-weight:800;margin-bottom:8px;display:block}
input,select{
    width:100%;
    padding:12px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.06);
    color:#fff;
    outline:none;
}
input::placeholder{color:rgba(255,255,255,.35)}
input:focus,select:focus{
    border-color:rgba(99,102,241,.55);
    box-shadow:0 0 0 4px rgba(99,102,241,.18);
}
.full{grid-column:1/-1}

.submit{
    width:100%;
    padding:14px;
    border:none;
    border-radius:14px;
    font-weight:900;
    cursor:pointer;
    color:#fff;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    transition:.25s;
}
.submit:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(99,102,241,.35)}

.muted{color:rgba(255,255,255,.45);font-size:12px;margin-top:10px}
.badge{
    display:inline-block;padding:6px 12px;border-radius:999px;
    background:rgba(99,102,241,.2);
    border:1px solid rgba(99,102,241,.35);
    color:#e0e7ff;font-weight:900;font-size:12px;
}
</style>
</head>

<body>
<div class="wrap">

    <div class="header">
        <h1>👨‍🎓 <?php echo $alumno_editar ? 'Editar' : 'Registrar'; ?> Alumno</h1>
        <a class="btn" href="index.php">🏠 Volver al Inicio</a>
    </div>

    <div class="card">

        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form class="form" method="POST">
            <?php if ($alumno_editar): ?>
                <input type="hidden" name="id_alumno" value="<?php echo (int)$alumno_editar['id_alumno']; ?>">
            <?php endif; ?>

            <div>
                <label>Nombre</label>
                <input name="nombre" required
                       value="<?php echo $alumno_editar ? htmlspecialchars($alumno_editar['nombre']) : ''; ?>"
                       placeholder="Ej: Juan">
            </div>

            <div>
                <label>Apellido Paterno</label>
                <input name="apellido_paterno" required
                       value="<?php echo $alumno_editar ? htmlspecialchars($alumno_editar['apellido_paterno']) : ''; ?>"
                       placeholder="Ej: Pérez">
            </div>

            <div>
                <label>Apellido Materno</label>
                <input name="apellido_materno" required
                       value="<?php echo $alumno_editar ? htmlspecialchars($alumno_editar['apellido_materno']) : ''; ?>"
                       placeholder="Ej: Martínez">
            </div>

            <div>
                <label>Grupo</label>
                <select name="id_grupo" required>
                    <option value="">Seleccione un grupo</option>

        <?php if ($grupos && $grupos->num_rows > 0): ?>
            <?php while($g = $grupos->fetch_assoc()): ?>
                <?php
                    $selected = ($alumno_editar && (int)$alumno_editar['id_grupo'] === (int)$g['id_grupo']) ? 'selected' : '';
                ?>
                <option value="<?php echo (int)$g['id_grupo']; ?>" <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($g['codigo_grupo'] . " - " . $g['carrera'] . " (" . $g['turno'] . ") - Grado " . $g['grado']); ?>
                </option>
            <?php endwhile; ?>
        <?php endif; ?>
    </select>

    <?php if (!$grupos || $grupos->num_rows === 0): ?>
        <div class="muted">No hay grupos activos. Primero registra un grupo.</div>
    <?php endif; ?>
</div>


            <div class="full">
                <button class="submit" type="submit">
                    <?php echo $alumno_editar ? 'Actualizar Alumno' : 'Registrar Alumno'; ?>
                </button>

                <?php if ($alumno_editar): ?>
                    <div class="muted">
                        <a href="registrar_alumno.php" style="color:#a5b4fc;text-decoration:none;font-weight:800;">
                            Cancelar edición
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </form>

    </div>
</div>
</body>
</html>
