<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';
$alumno_editar = null;

// Verificar si estamos editando un alumno
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM alumnos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $alumno_editar = $resultado->fetch_assoc();
    $stmt->close();
    $conn->close();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido_paterno = trim($_POST['apellido_paterno']);
    $apellido_materno = trim($_POST['apellido_materno']);
    $grupo_id = intval($_POST['grupo_id']);
    $id_alumno = isset($_POST['id_alumno']) ? intval($_POST['id_alumno']) : 0;
    
    if (!empty($nombre) && !empty($apellido_paterno) && !empty($apellido_materno) && $grupo_id > 0) {
        $conn = getConnection();
        
        if ($id_alumno > 0) {
            // Actualizar alumno existente
            $stmt = $conn->prepare("UPDATE alumnos SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, grupo_id = ? WHERE id = ?");
            $stmt->bind_param("sssii", $nombre, $apellido_paterno, $apellido_materno, $grupo_id, $id_alumno);
            
            if ($stmt->execute()) {
                $mensaje = "¡Alumno actualizado exitosamente!";
                $tipo_mensaje = 'exito';
                $alumno_editar = null;
            } else {
                $mensaje = "Error al actualizar el alumno: " . $conn->error;
                $tipo_mensaje = 'error';
            }
        } else {
            // Insertar nuevo alumno
            $stmt = $conn->prepare("INSERT INTO alumnos (nombre, apellido_paterno, apellido_materno, grupo_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $nombre, $apellido_paterno, $apellido_materno, $grupo_id);
            
            if ($stmt->execute()) {
                $mensaje = "¡Alumno registrado exitosamente!";
                $tipo_mensaje = 'exito';
            } else {
                $mensaje = "Error al registrar el alumno: " . $conn->error;
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

// Obtener todos los grupos disponibles
$conn = getConnection();
$grupos = $conn->query("SELECT * FROM grupos ORDER BY grupo");
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Alumno</title>
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
            max-width: 700px;
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
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .info-box p {
            margin: 0;
            color: #1976D2;
            font-size: 14px;
        }
        
        .alerta-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alerta-warning p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👨‍🎓 <?php echo $alumno_editar ? 'Editar' : 'Registrar'; ?> Alumno</h1>
        
        <div class="navegacion">
            <a href="registrar_grupo.php" class="nav-link">Registrar Grupo</a>
            <a href="registrar_alumno.php" class="nav-link active">Registrar Alumno</a>
            <a href="alumnos_registrados.php" class="nav-link">Ver Alumnos</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($grupos->num_rows == 0): ?>
            <div class="alerta-warning">
                <p>⚠️ No hay grupos registrados. Por favor, <a href="registrar_grupo.php">registre un grupo</a> primero.</p>
            </div>
        <?php else: ?>
            <?php if ($alumno_editar): ?>
                <div class="info-box">
                    <p>✏️ Editando alumno: <strong><?php echo htmlspecialchars($alumno_editar['nombre'] . ' ' . $alumno_editar['apellido_paterno']); ?></strong></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php if ($alumno_editar): ?>
                    <input type="hidden" name="id_alumno" value="<?php echo $alumno_editar['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?php echo $alumno_editar ? htmlspecialchars($alumno_editar['nombre']) : ''; ?>"
                           placeholder="Ej: Juan" required>
                </div>
                
                <div class="form-group">
                    <label for="apellido_paterno">Apellido Paterno</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" 
                           value="<?php echo $alumno_editar ? htmlspecialchars($alumno_editar['apellido_paterno']) : ''; ?>"
                           placeholder="Ej: Pérez" required>
                </div>
                
                <div class="form-group">
                    <label for="apellido_materno">Apellido Materno</label>
                    <input type="text" id="apellido_materno" name="apellido_materno" 
                           value="<?php echo $alumno_editar ? htmlspecialchars($alumno_editar['apellido_materno']) : ''; ?>"
                           placeholder="Ej: Martínez" required>
                </div>
                
                <div class="form-group">
                    <label for="grupo_id">Grupo</label>
                    <select id="grupo_id" name="grupo_id" required>
                        <option value="">Seleccione un grupo</option>
                        <?php 
                        $grupos->data_seek(0);
                        while($grupo = $grupos->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $grupo['id']; ?>"
                                    <?php echo ($alumno_editar && $alumno_editar['grupo_id'] == $grupo['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($grupo['grupo'] . ' - ' . $grupo['carrera'] . ' (' . $grupo['turno'] . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn">
                    <?php echo $alumno_editar ? 'Actualizar Alumno' : 'Registrar Alumno'; ?>
                </button>
                
                <?php if ($alumno_editar): ?>
                    <a href="registrar_alumno.php" style="display: block; text-align: center; margin-top: 15px; color: #667eea; text-decoration: none;">
                        Cancelar edición
                    </a>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
