<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

$conn = getConnection();

// Cargar catálogos activos
$carreras = $conn->query("SELECT id_carrera, nombre, codigo FROM carrera WHERE activo = 1 ORDER BY nombre");
$turnos   = $conn->query("SELECT id_turno, nombre, sigla FROM turno WHERE activo = 1 ORDER BY nombre");
$grados   = $conn->query("SELECT id_grado, numero FROM grado WHERE activo = 1 ORDER BY numero");

// Procesar alta de grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_carrera   = intval($_POST['id_carrera'] ?? 0);
    $id_turno     = intval($_POST['id_turno'] ?? 0);
    $id_grado     = intval($_POST['id_grado'] ?? 0);
    $numero_grupo = intval($_POST['numero_grupo'] ?? 0);

    if ($id_carrera && $id_turno && $id_grado && $numero_grupo) {

        // Obtener datos
        $stmt = $conn->prepare("SELECT codigo FROM carrera WHERE id_carrera=? AND activo=1");
        $stmt->bind_param("i", $id_carrera);
        $stmt->execute();
        $codigoCarrera = $stmt->get_result()->fetch_assoc()['codigo'] ?? null;
        $stmt->close();

        $stmt = $conn->prepare("SELECT sigla FROM turno WHERE id_turno=? AND activo=1");
        $stmt->bind_param("i", $id_turno);
        $stmt->execute();
        $siglaTurno = $stmt->get_result()->fetch_assoc()['sigla'] ?? null;
        $stmt->close();

        $stmt = $conn->prepare("SELECT numero FROM grado WHERE id_grado=? AND activo=1");
        $stmt->bind_param("i", $id_grado);
        $stmt->execute();
        $numeroGrado = $stmt->get_result()->fetch_assoc()['numero'] ?? null;
        $stmt->close();

        if (!$codigoCarrera || !$siglaTurno || !$numeroGrado) {
            $mensaje = "Catálogos inválidos o inactivos.";
            $tipo_mensaje = "error";
        } else {
            $grupo2 = str_pad($numero_grupo, 2, "0", STR_PAD_LEFT);
            $codigo_grupo = $codigoCarrera . $numeroGrado . $grupo2 . '-' . $siglaTurno;

            $stmt = $conn->prepare(
                "SELECT id_grupo FROM grupo 
                 WHERE id_carrera=? AND id_turno=? AND id_grado=? AND numero_grupo=?"
            );
            $stmt->bind_param("iiii", $id_carrera, $id_turno, $id_grado, $numero_grupo);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($existe) {
                $mensaje = "El grupo {$codigo_grupo} ya existe.";
                $tipo_mensaje = "error";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO grupo (id_carrera,id_turno,id_grado,numero_grupo,codigo_grupo)
                     VALUES (?,?,?,?,?)"
                );
                $stmt->bind_param(
                    "iiiis",
                    $id_carrera,
                    $id_turno,
                    $id_grado,
                    $numero_grupo,
                    $codigo_grupo
                );

                if ($stmt->execute()) {
                    $mensaje = "Grupo {$codigo_grupo} registrado correctamente";
                    $tipo_mensaje = "exito";
                } else {
                    $mensaje = "Error al registrar el grupo";
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
        }
    } else {
        $mensaje = "Completa todos los campos";
        $tipo_mensaje = "error";
    }
}

// Listado
$grupos = $conn->query("
    SELECT g.codigo_grupo, g.fecha_registro,
           c.nombre carrera, t.nombre turno, gr.numero grado
    FROM grupo g
    INNER JOIN carrera c ON g.id_carrera=c.id_carrera
    INNER JOIN turno t ON g.id_turno=t.id_turno
    INNER JOIN grado gr ON g.id_grado=gr.id_grado
    ORDER BY g.fecha_registro DESC
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Grupo</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
*{margin:0;padding:0;box-sizing:border-box}

body{
    font-family:'Inter',sans-serif;
    background:#0f0f1e;
    min-height:100vh;
    padding:25px;
}
body::before{
    content:'';
    position:fixed;inset:0;
    background:
        radial-gradient(circle at 20% 50%, rgba(120,119,198,.3), transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(99,102,241,.2), transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(168,85,247,.15), transparent 50%);
}

.wrap{max-width:1100px;margin:auto;position:relative;z-index:1}

.header{
    display:flex;justify-content:space-between;align-items:center;
    margin-bottom:20px;flex-wrap:wrap;gap:15px;
}
.header h1{color:#fff;font-size:22px;font-weight:800}
.btn{
    padding:12px 18px;
    border-radius:12px;
    text-decoration:none;
    font-weight:700;
    color:#fff;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    transition:.25s;
}
.btn:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(99,102,241,.35)}

.card{
    background:rgba(15,15,30,.55);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.1);
    border-radius:18px;
    padding:25px;
    box-shadow:0 20px 60px rgba(0,0,0,.35);
}

.mensaje{
    padding:12px 14px;border-radius:12px;
    margin-bottom:18px;font-weight:700;font-size:13px
}
.exito{background:rgba(34,197,94,.15);color:#bbf7d0}
.error{background:rgba(239,68,68,.15);color:#fecaca}

form{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
}
label{color:#c7d2fe;font-size:13px;font-weight:700}
select,input{
    width:100%;padding:12px;border-radius:12px;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.06);color:#fff;
}
button{
    grid-column:1/-1;
    padding:14px;border:none;border-radius:14px;
    font-weight:800;font-size:15px;
    color:#fff;cursor:pointer;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
}
button:hover{box-shadow:0 14px 30px rgba(99,102,241,.35)}

table{
    width:100%;margin-top:30px;border-collapse:collapse
}
th,td{padding:12px;border-bottom:1px solid rgba(255,255,255,.1)}
th{color:#fff;font-size:13px}
td{color:#c7d2fe;font-size:13px}
.badge{
    padding:6px 12px;border-radius:999px;
    background:rgba(99,102,241,.2);
    border:1px solid rgba(99,102,241,.4);
    font-weight:800;color:#e0e7ff
}
</style>
</head>

<body>
<div class="wrap">

<div class="header">
    <h1>📚 Registrar Grupo</h1>
    <a href="index.php" class="btn">🏠 Volver al Inicio</a>
</div>

<div class="card">

<?php if($mensaje): ?>
    <div class="mensaje <?php echo $tipo_mensaje; ?>">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
<?php endif; ?>

<form method="POST">
    <div>
        <label>Carrera</label>
        <select name="id_carrera" required>
            <option value="">Seleccione</option>
            <?php while($c=$carreras->fetch_assoc()): ?>
                <option value="<?php echo $c['id_carrera']; ?>">
                    <?php echo htmlspecialchars($c['nombre']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div>
        <label>Turno</label>
        <select name="id_turno" required>
            <option value="">Seleccione</option>
            <?php while($t=$turnos->fetch_assoc()): ?>
                <option value="<?php echo $t['id_turno']; ?>">
                    <?php echo htmlspecialchars($t['nombre']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div>
        <label>Grado</label>
        <select name="id_grado" required>
            <option value="">Seleccione</option>
            <?php while($g=$grados->fetch_assoc()): ?>
                <option value="<?php echo $g['id_grado']; ?>">
                    <?php echo htmlspecialchars($g['numero']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div>
        <label>Número de Grupo</label>
        <input type="number" name="numero_grupo" min="1" max="99" required>
    </div>

    <button type="submit">Registrar Grupo</button>
</form>

<?php if($grupos->num_rows): ?>
<table>
<thead>
<tr>
    <th>Código</th><th>Carrera</th><th>Turno</th><th>Grado</th><th>Fecha</th>
</tr>
</thead>
<tbody>
<?php while($r=$grupos->fetch_assoc()): ?>
<tr>
    <td><span class="badge"><?php echo $r['codigo_grupo']; ?></span></td>
    <td><?php echo $r['carrera']; ?></td>
    <td><?php echo $r['turno']; ?></td>
    <td><?php echo $r['grado']; ?></td>
    <td><?php echo date('d/m/Y H:i',strtotime($r['fecha_registro'])); ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php endif; ?>

</div>
</div>
</body>
</html>
