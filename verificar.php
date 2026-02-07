<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 40px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            font-weight: 600;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 Verificación del Sistema de Gestión Escolar</h1>
        <p>Esta página verifica que todos los componentes estén correctamente instalados.</p>
    </div>

    <div class="card">
        <h2>📁 Archivos del Sistema</h2>
        <?php
        $archivos_requeridos = [
            'config.php' => 'Configuración de base de datos',
            'index.php' => 'Página principal',
            'configurar_catalogos.php' => 'Catálogo de carreras',
            'registrar_carrera.php' => 'Registrar carrera',
            'registrar_grupo.php' => 'Registrar grupo',
            'registrar_alumno.php' => 'Registrar alumno',
            'alumnos_registrados.php' => 'Ver alumnos'
        ];

        echo '<table>';
        echo '<tr><th>Archivo</th><th>Descripción</th><th>Estado</th></tr>';
        
        foreach ($archivos_requeridos as $archivo => $descripcion) {
            $existe = file_exists($archivo);
            $estado = $existe ? '<span class="success">✓ Existe</span>' : '<span class="error">✗ No encontrado</span>';
            echo "<tr><td><strong>$archivo</strong></td><td>$descripcion</td><td>$estado</td></tr>";
        }
        
        echo '</table>';
        ?>
    </div>

    <div class="card">
        <h2>🗄️ Conexión a Base de Datos</h2>
        <?php
        if (file_exists('config.php')) {
            require_once 'config.php';
            
            try {
                $conn = getConnection();
                echo '<p class="success">✓ Conexión exitosa a la base de datos</p>';
                
                // Verificar tablas
                $tablas = ['carreras', 'grupos', 'alumnos'];
                echo '<h3>Tablas de la Base de Datos:</h3>';
                echo '<table>';
                echo '<tr><th>Tabla</th><th>Registros</th><th>Estado</th></tr>';
                
                foreach ($tablas as $tabla) {
                    $result = $conn->query("SELECT COUNT(*) as total FROM $tabla");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        echo "<tr><td><strong>$tabla</strong></td><td>{$row['total']}</td><td><span class='success'>✓ OK</span></td></tr>";
                    } else {
                        echo "<tr><td><strong>$tabla</strong></td><td>-</td><td><span class='error'>✗ Error</span></td></tr>";
                    }
                }
                
                echo '</table>';
                $conn->close();
                
            } catch (Exception $e) {
                echo '<p class="error">✗ Error de conexión: ' . $e->getMessage() . '</p>';
            }
        } else {
            echo '<p class="error">✗ Archivo config.php no encontrado</p>';
        }
        ?>
    </div>

    <div class="card">
        <h2>🌐 Información del Servidor</h2>
        <table>
            <tr>
                <td><strong>Servidor Web:</strong></td>
                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
            </tr>
            <tr>
                <td><strong>Versión de PHP:</strong></td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td><strong>Directorio actual:</strong></td>
                <td><?php echo __DIR__; ?></td>
            </tr>
            <tr>
                <td><strong>URL de acceso:</strong></td>
                <td><?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']); ?></td>
            </tr>
            <tr>
                <td><strong>Ruta del script:</strong></td>
                <td><?php echo $_SERVER['SCRIPT_NAME']; ?></td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>🚀 Acciones Rápidas</h2>
        <div class="info">
            <strong>ℹ️ Importante:</strong> Si todos los archivos existen y la base de datos está conectada, 
            el sistema debería funcionar correctamente.
        </div>
        
        <h3>Enlaces de Navegación:</h3>
        <a href="index.php" class="btn">🏠 Ir al Index</a>
        <a href="configurar_catalogos.php" class="btn">⚙️ Catálogo Carreras</a>
        <a href="registrar_grupo.php" class="btn">📚 Registrar Grupo</a>
        <a href="registrar_alumno.php" class="btn">➕ Registrar Alumno</a>
        <a href="alumnos_registrados.php" class="btn">👥 Ver Alumnos</a>
    </div>

    <div class="card">
        <h2>📋 Instrucciones de Uso</h2>
        <ol style="line-height: 2;">
            <li>Asegúrate de que XAMPP esté ejecutando <strong>Apache</strong> y <strong>MySQL</strong></li>
            <li>Todos los archivos PHP deben estar en: <code>C:\xampp\htdocs\pp\</code></li>
            <li>Accede al sistema mediante: <code>http://localhost/pp/</code></li>
            <li>Si algún archivo aparece como "No encontrado", cópialo desde tu repositorio Git</li>
            <li>Si la base de datos no conecta, verifica las credenciales en <code>config.php</code></li>
        </ol>
    </div>
</body>
</html>