<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

$conn = getConnection();

// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';

    if ($nombre !== '' && $codigo !== '') {

        // Verificar si ya existe la carrera
        $stmt = $conn->prepare("SELECT id_carrera FROM carrera WHERE codigo = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $mensaje = "La carrera con código $codigo ya existe";
            $tipo_mensaje = "error";
        } else {
            // Insertar carrera
            $stmt = $conn->prepare("INSERT INTO carrera (nombre, codigo, activo) VALUES (?, ?, 1)");
            $stmt->bind_param("ss", $nombre, $codigo);

            if ($stmt->execute()) {
                $mensaje = "Carrera registrada correctamente";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "Error al registrar carrera";
                $tipo_mensaje = "error";
            }
        }

        $stmt->close();
    } else {
        $mensaje = "Completa todos los campos";
        $tipo_mensaje = "error";
    }
}

// LISTAR CARRERAS
$carreras = $conn->query("SELECT * FROM carrera ORDER BY nombre");
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Carrera</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        *{margin:0;padding:0;box-sizing:border-box}

        body{
            font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
            background:#0f0f1e;
            min-height:100vh;
            padding:22px;
            position:relative;
        }

        /* Fondo estilo index */
        body::before{
            content:'';
            position:fixed;
            top:0;left:0;width:100%;height:100%;
            background:
                radial-gradient(circle at 20% 50%, rgba(120,119,198,.30) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(99,102,241,.20) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(168,85,247,.15) 0%, transparent 50%);
            z-index:0;
        }

        .wrap{
            max-width:1050px;
            margin:0 auto;
            position:relative;
            z-index:1;
        }

        .topbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:14px;
            flex-wrap:wrap;
            margin-bottom:18px;
        }

        .title{
            display:flex;
            align-items:center;
            gap:12px;
        }
        .logo{
            width:46px;height:46px;
            border-radius:12px;
            background:linear-gradient(135deg,#6366f1 0%, #8b5cf6 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:24px;
            box-shadow:0 10px 25px rgba(99,102,241,.25);
        }
        .title h1{
            color:#fff;
            font-size:22px;
            font-weight:800;
            letter-spacing:-.4px;
        }
        .title p{
            color:rgba(255,255,255,.55);
            font-size:13px;
            margin-top:2px;
        }

        .btns{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .btn{
            padding:11px 16px;
            border-radius:10px;
            text-decoration:none;
            font-weight:600;
            font-size:14px;
            display:inline-flex;
            align-items:center;
            gap:8px;
            border:1px solid rgba(255,255,255,.12);
            background:rgba(255,255,255,.06);
            color:rgba(255,255,255,.9);
            backdrop-filter:blur(14px);
            transition:all .25s ease;
        }
        .btn:hover{
            transform:translateY(-2px);
            border-color:rgba(99,102,241,.45);
            box-shadow:0 12px 26px rgba(99,102,241,.18);
            background:rgba(255,255,255,.10);
        }
        .btn.primary{
            background:linear-gradient(135deg,#6366f1 0%, #8b5cf6 100%);
            border-color:rgba(99,102,241,.45);
            color:#fff;
        }
        .btn.primary:hover{
            box-shadow:0 14px 30px rgba(99,102,241,.30);
        }

        .card{
            background:rgba(15,15,30,.52);
            backdrop-filter:blur(22px);
            border:1px solid rgba(255,255,255,.10);
            border-radius:18px;
            box-shadow:0 16px 60px rgba(0,0,0,.30);
            padding:24px;
            animation:fadeInUp .5s ease both;
        }

        @keyframes fadeInUp{
            from{opacity:0;transform:translateY(18px)}
            to{opacity:1;transform:translateY(0)}
        }

        .section-title{
            color:rgba(255,255,255,.95);
            font-size:16px;
            font-weight:700;
            margin-bottom:14px;
            display:flex;
            align-items:center;
            gap:10px;
        }

        .form{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:14px;
            margin-bottom:18px;
        }

        label{
            display:block;
            color:rgba(255,255,255,.75);
            font-size:13px;
            font-weight:600;
            margin-bottom:8px;
        }

        input{
            width:100%;
            padding:12px 12px;
            border-radius:12px;
            border:1px solid rgba(255,255,255,.12);
            background:rgba(255,255,255,.06);
            color:#fff;
            outline:none;
            transition:border .2s ease, box-shadow .2s ease;
        }
        input::placeholder{color:rgba(255,255,255,.35)}
        input:focus{
            border-color:rgba(99,102,241,.55);
            box-shadow:0 0 0 4px rgba(99,102,241,.18);
        }

        .full{grid-column:1 / -1;}

        .submit{
            width:100%;
            padding:14px 16px;
            border:none;
            border-radius:12px;
            cursor:pointer;
            font-weight:800;
            letter-spacing:.2px;
            color:#fff;
            background:linear-gradient(135deg,#6366f1 0%, #8b5cf6 100%);
            transition:all .25s ease;
        }
        .submit:hover{
            transform:translateY(-2px);
            box-shadow:0 14px 30px rgba(99,102,241,.30);
        }

        .mensaje{
            margin:14px 0 18px;
            padding:12px 14px;
            border-radius:12px;
            font-weight:700;
            font-size:13px;
            border:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.06);
            color:rgba(255,255,255,.9);
        }
        .mensaje.exito{
            border-color:rgba(34,197,94,.35);
            background:rgba(34,197,94,.10);
            color:#bbf7d0;
        }
        .mensaje.error{
            border-color:rgba(239,68,68,.35);
            background:rgba(239,68,68,.10);
            color:#fecaca;
        }

        table{
            width:100%;
            border-collapse:collapse;
            overflow:hidden;
            border-radius:14px;
        }
        th,td{
            padding:12px 12px;
            border-bottom:1px solid rgba(255,255,255,.08);
            text-align:left;
        }
        th{
            background:rgba(255,255,255,.06);
            color:rgba(255,255,255,.9);
            font-size:13px;
            font-weight:800;
        }
        td{
            color:rgba(255,255,255,.75);
            font-size:13px;
        }
        tr:hover td{
            background:rgba(255,255,255,.04);
        }

        .badge{
            display:inline-block;
            padding:6px 12px;
            border-radius:999px;
            background:linear-gradient(135deg, rgba(99,102,241,.20) 0%, rgba(139,92,246,.20) 100%);
            border:1px solid rgba(99,102,241,.35);
            color:#c7d2fe;
            font-size:12px;
            font-weight:800;
            letter-spacing:.4px;
        }

        .muted{
            color:rgba(255,255,255,.45);
            font-size:12px;
            margin-top:8px;
        }

        @media (max-width: 820px){
            .form{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
<div class="wrap">

    <div class="topbar">
        <div class="title">
            <div class="logo">🎓</div>
            <div>
                <h1>Registrar Carrera</h1>
                <p>Catálogo de carreras (nombre, código y estado)</p>
            </div>
        </div>

        <div class="btns">
            <a class="btn primary" href="index.php">🏠 Volver al Inicio</a>
        </div>
    </div>

    <div class="card">
        <div class="section-title">📝 Alta de carrera</div>

        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form class="form" method="POST">
            <div>
                <label>Nombre de la carrera</label>
                <input name="nombre" placeholder="Ej: Sistemas Computacionales" required>
            </div>

            <div>
                <label>Código (3 letras)</label>
                <input name="codigo" placeholder="Ej: ISC" maxlength="5" required>
            </div>

            <div class="full">
                <button class="submit" type="submit">Guardar Carrera</button>
                <div class="muted">Tip: Usa un código corto (ISC, TUR, ADM...).</div>
            </div>
        </form>

        <div class="section-title">📋 Carreras registradas</div>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($c = $carreras->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                        <td><span class="badge"><?php echo htmlspecialchars($c['codigo']); ?></span></td>
                        <td><?php echo ((int)$c['activo'] === 1) ? 'Activa' : 'Inactiva'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>
</body>
</html>
