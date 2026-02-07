<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar eliminación de alumno
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM alumnos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje = "Alumno eliminado exitosamente";
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = "Error al eliminar el alumno";
        $tipo_mensaje = 'error';
    }
    
    $stmt->close();
    $conn->close();
}

// Obtener todos los alumnos con información de sus grupos
$conn = getConnection();
$sql = "SELECT a.*, g.grupo, g.carrera, g.turno 
        FROM alumnos a 
        INNER JOIN grupos g ON a.grupo_id = g.id 
        ORDER BY a.fecha_registro DESC";
$alumnos = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos Registrados</title>
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
            max-width: 1200px;
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
        
        .tabla-contenedor {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 15px;
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
        
        .acciones {
            display: flex;
            gap: 8px;
        }
        
        .btn-editar, .btn-eliminar {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-editar {
            background: #28a745;
            color: white;
        }
        
        .btn-editar:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-eliminar {
            background: #dc3545;
            color: white;
        }
        
        .btn-eliminar:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-state h2 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }
        
        .empty-state a {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .empty-state a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .estadisticas {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stat-card {
            text-align: center;
        }
        
        .stat-numero {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
    <script>
        function confirmarEliminacion(nombre) {
            return confirm('¿Está seguro de que desea eliminar al alumno ' + nombre + '?');
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>📋 Alumnos Registrados</h1>
        
        <div class="navegacion">
            <a href="configurar_catalogos.php" class="nav-link">Catálogo Carreras</a>
            <a href="registrar_grupo.php" class="nav-link">Registrar Grupo</a>
            <a href="registrar_alumno.php" class="nav-link">Registrar Alumno</a>
            <a href="alumnos_registrados.php" class="nav-link active">Ver Alumnos</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($alumnos->num_rows > 0): ?>
            <?php
            // Calcular estadísticas
            $total_alumnos = $alumnos->num_rows;
            $conn = getConnection();
            $total_grupos = $conn->query("SELECT COUNT(DISTINCT grupo_id) as total FROM alumnos")->fetch_assoc()['total'];
            $conn->close();
            ?>
            
            <div class="estadisticas">
                <div class="stat-card">
                    <div class="stat-numero"><?php echo $total_alumnos; ?></div>
                    <div class="stat-label">Total Alumnos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-numero"><?php echo $total_grupos; ?></div>
                    <div class="stat-label">Grupos con Alumnos</div>
                </div>
            </div>
            
            <div class="tabla-contenedor">
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
                                <td><?php echo $alumno['id']; ?></td>
                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($alumno['nombre'] . ' ' . 
                                                                    $alumno['apellido_paterno'] . ' ' . 
                                                                    $alumno['apellido_materno']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge"><?php echo htmlspecialchars($alumno['grupo']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($alumno['carrera']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['turno']); ?></td>
                                <td>
                                    <div class="acciones">
                                        <a href="registrar_alumno.php?editar=<?php echo $alumno['id']; ?>" 
                                           class="btn-editar">
                                            ✏️ Editar
                                        </a>
                                        <a href="?eliminar=<?php echo $alumno['id']; ?>" 
                                           class="btn-eliminar"
                                           onclick="return confirmarEliminacion('<?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido_paterno']); ?>')">
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
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <h2>No hay alumnos registrados</h2>
                <p>Comienza agregando tu primer alumno al sistema</p>
                <a href="registrar_alumno.php">➕ Registrar Alumno</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>