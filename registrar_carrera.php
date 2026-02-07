<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    
    if (!empty($nombre)) {
        $conn = getConnection();
        
        // Verificar si la carrera ya existe
        $stmt = $conn->prepare("SELECT id FROM carreras WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $mensaje = "¡Error! La carrera '$nombre' ya existe en el catálogo.";
            $tipo_mensaje = 'error';
        } else {
            // Insertar la nueva carrera
            $stmt = $conn->prepare("INSERT INTO carreras (nombre, activa) VALUES (?, 1)");
            $stmt->bind_param("s", $nombre);
            
            if ($stmt->execute()) {
                $mensaje = "¡Carrera '$nombre' registrada exitosamente!";
                $tipo_mensaje = 'exito';
                // Limpiar el formulario
                $_POST = array();
            } else {
                $mensaje = "Error al registrar la carrera: " . $conn->error;
                $tipo_mensaje = 'error';
            }
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $mensaje = "Por favor ingrese el nombre de la carrera";
        $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Carrera</title>
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
            max-width: 600px;
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
            flex-wrap: wrap;
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
        
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border 0.3s;
        }
        
        input:focus {
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
            margin-bottom: 15px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secundario {
            background: #6c757d;
        }
        
        .btn-secundario:hover {
            background: #5a6268;
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
        
        .ejemplos {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .ejemplos p {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 13px;
            font-weight: 600;
        }
        
        .ejemplos ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .ejemplos li {
            color: #999;
            font-size: 13px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Registrar Carrera</h1>
        
        <div class="navegacion">
            <a href="configurar_catalogos.php" class="nav-link">Catálogo Carreras</a>
            <a href="registrar_grupo.php" class="nav-link">Registrar Grupo</a>
            <a href="registrar_alumno.php" class="nav-link">Registrar Alumno</a>
            <a href="alumnos_registrados.php" class="nav-link">Ver Alumnos</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <p>ℹ️ Registre las carreras que se ofrecen en la institución. Posteriormente podrá asignar grupos a estas carreras.</p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre de la Carrera</label>
                <input type="text" 
                       id="nombre" 
                       name="nombre" 
                       placeholder="Ej: Sistemas, Psicología, Pedagogía" 
                       required 
                       autofocus>
                
                <div class="ejemplos">
                    <p>Ejemplos de carreras:</p>
                    <ul>
                        <li>Sistemas</li>
                        <li>Psicología</li>
                        <li>Pedagogía</li>
                        <li>Administración</li>
                        <li>Contabilidad</li>
                    </ul>
                </div>
            </div>
            
            <button type="submit" class="btn">Registrar Carrera</button>
            <a href="configurar_catalogos.php" class="btn btn-secundario" style="display: block; text-align: center; text-decoration: none; line-height: 1.5;">
                ← Volver al Catálogo
            </a>
        </form>
    </div>
</body>
</html>