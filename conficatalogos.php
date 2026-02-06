<?php
require_once 'config.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar eliminación de carrera
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn = getConnection();
    
    // Verificar si la carrera está siendo usada en grupos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM grupos WHERE carrera = (SELECT nombre FROM carreras WHERE id = ?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    
    if ($resultado['total'] > 0) {
        $mensaje = "No se puede eliminar esta carrera porque tiene grupos asociados";
        $tipo_mensaje = 'error';
    } else {
        $stmt = $conn->prepare("DELETE FROM carreras WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $mensaje = "Carrera eliminada exitosamente";
            $tipo_mensaje = 'exito';
        } else {
            $mensaje = "Error al eliminar la carrera";
            $tipo_mensaje = 'error';
        }
    }
    
    $stmt->close();
    $conn->close();
}

// Procesar activar/desactivar carrera
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn = getConnection();
    
    $stmt = $conn->prepare("UPDATE carreras SET activa = NOT activa WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje = "Estado de carrera actualizado exitosamente";
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = "Error al actualizar el estado de la carrera";
        $tipo_mensaje = 'error';
    }
    
    $stmt->close();
    $conn->close();
}

// Obtener todas las carreras
$conn = getConnection();
$carreras = $conn->query("SELECT c.*, COUNT(g.id) as total_grupos 
                          FROM carreras c 
                          LEFT JOIN grupos g ON c.nombre = g.carrera 
                          GROUP BY c.id 
                          ORDER BY c.nombre");
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Catálogos - Carreras</title>
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
            max-width: 1000px;
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
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .btn-registrar {
            padding: 12px 25px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-registrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
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
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-activa {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactiva {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-grupos {
            background: #cce5ff;
            color: #004085;
        }
        
        .acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-accion {
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
        
        .btn-activar {
            background: #28a745;
            color: white;
        }
        
        .btn-activar:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-desactivar {
            background: #ffc107;
            color: #333;
        }
        
        .btn-desactivar:hover {
            background: #e0a800;
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
        function confirmarEliminacion(nombre, grupos) {
            if (grupos > 0) {
                alert('No se puede eliminar la carrera "' + nombre + '" porque tiene ' + grupos + ' grupo(s) asociado(s).');
                return false;
            }
            return confirm('¿Está seguro de que desea eliminar la carrera "' + nombre + '"?');
        }
        
        function confirmarCambioEstado(nombre, activa) {
            const accion = activa ? 'desactivar' : 'activar';
            return confirm('¿Está seguro de que desea ' + accion + ' la carrera "' + nombre + '"?');
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>⚙️ Configurar Catálogos - Carreras</h1>
        
        <div class="navegacion">
            <a href="configurar_catalogos.php" class="nav-link active">Catálogo Carreras</a>
            <a href="registrar_grupo.php" class="nav-link">Registrar Grupo</a>
            <a href="registrar_alumno.php" class="nav-link">Registrar Alumno</a>
            <a href="alumnos_registrados.php" class="nav-link">Ver Alumnos</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="header-actions">
            <h2 style="color: #333; font-size: 20px;">Lista de Carreras</h2>
            <a href="registrar_carrera.php" class="btn-registrar">
                ➕ Registrar Carrera
            </a>
        </div>
        
        <?php if ($carreras->num_rows > 0): ?>
            <?php
            // Calcular estadísticas
            $total_carreras = $carreras->num_rows;
            $carreras_activas = 0;
            $carreras_inactivas = 0;
            
            // Contar carreras activas e inactivas
            $carreras->data_seek(0);
            while($c = $carreras->fetch_assoc()) {
                if ($c['activa']) {
                    $carreras_activas++;
                } else {
                    $carreras_inactivas++;
                }
            }
            ?>
            
            <div class="estadisticas">
                <div class="stat-card">
                    <div class="stat-numero"><?php echo $total_carreras; ?></div>
                    <div class="stat-label">Total Carreras</div>
                </div>
                <div class="stat-card">
                    <div class="stat-numero" style="color: #28a745;"><?php echo $carreras_activas; ?></div>
                    <div class="stat-label">Activas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-numero" style="color: #dc3545;"><?php echo $carreras_inactivas; ?></div>
                    <div class="stat-label">Inactivas</div>
                </div>
            </div>
            
            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            <th>Estado</th>
                            <th>Grupos Asociados</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $carreras->data_seek(0);
                        while($carrera = $carreras->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($carrera['nombre']); ?></strong></td>
                                <td>
                                    <?php if ($carrera['activa']): ?>
                                        <span class="badge badge-activa">✓ Activa</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactiva">✗ Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-grupos">
                                        <?php echo $carrera['total_grupos']; ?> grupo(s)
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($carrera['fecha_registro'])); ?></td>
                                <td>
                                    <div class="acciones">
                                        <?php if ($carrera['activa']): ?>
                                            <a href="?toggle=<?php echo $carrera['id']; ?>" 
                                               class="btn-accion btn-desactivar"
                                               onclick="return confirmarCambioEstado('<?php echo htmlspecialchars($carrera['nombre']); ?>', true)">
                                                🔒 Desactivar
                                            </a>
                                        <?php else: ?>
                                            <a href="?toggle=<?php echo $carrera['id']; ?>" 
                                               class="btn-accion btn-activar"
                                               onclick="return confirmarCambioEstado('<?php echo htmlspecialchars($carrera['nombre']); ?>', false)">
                                                ✓ Activar
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="?eliminar=<?php echo $carrera['id']; ?>" 
                                           class="btn-accion btn-eliminar"
                                           onclick="return confirmarEliminacion('<?php echo htmlspecialchars($carrera['nombre']); ?>', <?php echo $carrera['total_grupos']; ?>)">
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
                <h2>No hay carreras registradas</h2>
                <p>Comienza agregando tu primera carrera al catálogo</p>
                <a href="registrar_carrera.php" class="btn-registrar">➕ Registrar Carrera</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>