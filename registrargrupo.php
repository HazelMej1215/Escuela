<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrera = trim($_POST['carrera']);
    $turno = trim($_POST['turno']);
    $grado = trim($_POST['grado']);
    $grupo = trim($_POST['grupo']);
    
    if (!empty($carrera) && !empty($turno) && !empty($grado) && !empty($grupo)) {
        $conn = getConnection();
        
        // Verificar si el grupo ya existe
        $stmt = $conn->prepare("SELECT id FROM grupos WHERE grupo = ?");
        $stmt->bind_param("s", $grupo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $mensaje = "¡Error! El grupo '$grupo' ya existe. Por favor use otro nombre (ej. ISC802, ISC803, etc.)";
            $tipo_mensaje = 'error';
        } else {
            // Insertar el nuevo grupo
            $stmt = $conn->prepare("INSERT INTO grupos (carrera, turno, grado, grupo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $carrera, $turno, $grado, $grupo);
            
            if ($stmt->execute()) {
                $mensaje = "¡Grupo '$grupo' registrado exitosamente!";
                $tipo_mensaje = 'exito';
            } else {
                $mensaje = "Error al registrar el grupo: " . $conn->error;
                $tipo_mensaje = 'error';
            }
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $mensaje = "Por favor complete todos los campos";
        $tipo_mensaje = 'error';
    }
}

// Obtener todos los grupos registrados
$conn = getConnection();
$grupos_registrados = $conn->query("SELECT * FROM grupos ORDER BY fecha_registro DESC");
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Grupo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .navegacion {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .nav-link {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .nav-link:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            background: #764ba2;
        }
        
        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .mensaje.exito {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .grupos-lista {
            margin-top: 30px;
        }
        
        .grupos-lista h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            background: #667eea;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Registrar Grupo</h1>
        
        <div class="navegacion">
            <a href="registrar_grupo.php" class="nav-link active">Registrar Grupo</a>
            <a href="registrar_alumno.php" class="nav-link">Registrar Alumno</a>
            <a href="alumnos_registrados.php" class="nav-link">Ver Alumnos</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="carrera">Carrera / Sistemas</label>
                <input type="text" id="carrera" name="carrera" placeholder="Ej: Sistemas" required>
            </div>
            
            <div class="form-group">
                <label for="turno">Turno</label>
                <select id="turno" name="turno" required>
                    <option value="">Seleccione un turno</option>
                    <option value="Vespertino">Vespertino</option>
                    <option value="Matutino">Matutino</option>
                    <option value="Nocturno">Nocturno</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="grado">Grado</label>
                <input type="text" id="grado" name="grado" placeholder="Ej: 8" required>
            </div>
            
            <div class="form-group">
                <label for="grupo">Grupo (código único)</label>
                <input type="text" id="grupo" name="grupo" placeholder="Ej: ISC801-V" required>
                <small style="color: #999; font-size: 12px; display: block; margin-top: 5px;">
                    Este código debe ser único. No se puede repetir.
                </small>
            </div>
            
            <button type="submit" class="btn">Registrar Grupo</button>
        </form>
        
        <div class="grupos-lista">
            <h2>Grupos Registrados</h2>
            
            <?php if ($grupos_registrados->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            <th>Turno</th>
                            <th>Grado</th>
                            <th>Grupo</th>
                            <th>Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($grupo = $grupos_registrados->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grupo['carrera']); ?></td>
                                <td><?php echo htmlspecialchars($grupo['turno']); ?></td>
                                <td><?php echo htmlspecialchars($grupo['grado']); ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($grupo['grupo']); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($grupo['fecha_registro'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No hay grupos registrados aún.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>