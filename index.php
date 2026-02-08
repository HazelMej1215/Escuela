<?php
require_once 'config.php';

// Obtener estadísticas del sistema
$conn = getConnection();

// Total de carreras
$total_carreras = $conn->query("SELECT COUNT(*) as total FROM carreras")->fetch_assoc()['total'];
$carreras_activas = $conn->query("SELECT COUNT(*) as total FROM carreras WHERE activa = 1")->fetch_assoc()['total'];

// Total de grupos
$total_grupos = $conn->query("SELECT COUNT(*) as total FROM grupos")->fetch_assoc()['total'];

// Total de alumnos
$total_alumnos = $conn->query("SELECT COUNT(*) as total FROM alumnos")->fetch_assoc()['total'];

// Últimos registros
$ultimos_alumnos = $conn->query("SELECT a.*, g.grupo, g.carrera 
                                 FROM alumnos a 
                                 INNER JOIN grupos g ON a.grupo_id = g.id 
                                 ORDER BY a.fecha_registro DESC 
                                 LIMIT 5");

$ultimos_grupos = $conn->query("SELECT * FROM grupos ORDER BY fecha_registro DESC LIMIT 5");

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Escolar</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f0f1e;
            min-height: 100vh;
            position: relative;
        }

        /* Fondo animado */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(168, 85, 247, 0.15) 0%, transparent 50%);
            z-index: 0;
        }

        /* Header y Navegación */
        .header {
            background: rgba(15, 15, 30, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 24px 0;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        .logo-text h1 {
            background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .logo-text p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            margin-top: 4px;
            font-weight: 400;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 11px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(99, 102, 241, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.2);
        }

        .nav-btn.primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: 1px solid rgba(99, 102, 241, 0.5);
            color: white;
        }

        .nav-btn.primary:hover {
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
            border-color: rgba(139, 92, 246, 0.5);
        }

        /* Contenido Principal */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 50px 40px;
            position: relative;
            z-index: 1;
        }

        /* Banner de Bienvenida */
        .welcome-banner {
            background: rgba(15, 15, 30, 0.5);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 60px 50px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                #6366f1 20%, 
                #8b5cf6 50%, 
                #6366f1 80%, 
                transparent 100%
            );
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-banner h2 {
            background: linear-gradient(135deg, #ffffff 0%, #c7d2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }

        .welcome-banner p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 18px;
            margin-bottom: 35px;
            font-weight: 400;
        }

        .quick-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .quick-action-btn {
            padding: 16px 32px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
        }

        .quick-action-btn:hover {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-color: rgba(99, 102, 241, 0.5);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.3);
        }

        /* Tarjetas de Estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: rgba(15, 15, 30, 0.5);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 20px 50px rgba(99, 102, 241, 0.2);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-number {
            font-size: 48px;
            font-weight: 800;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            letter-spacing: -2px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .stat-sublabel {
            color: rgba(255, 255, 255, 0.4);
            font-size: 13px;
            font-weight: 400;
        }

        /* Secciones de Últimos Registros */
        .recent-section {
            background: rgba(15, 15, 30, 0.5);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .recent-section h3 {
            color: rgba(255, 255, 255, 0.95);
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .recent-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .recent-item {
            background: rgba(255, 255, 255, 0.03);
            padding: 24px;
            border-radius: 14px;
            border-left: 3px solid transparent;
            border-image: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%) 1;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .recent-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%);
            transition: width 0.3s;
        }

        .recent-item:hover::before {
            width: 100%;
            opacity: 0.05;
        }

        .recent-item:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(8px);
        }

        .recent-item-header {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 12px;
            font-size: 16px;
        }

        .recent-item-detail {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .recent-item-date {
            color: rgba(255, 255, 255, 0.35);
            font-size: 12px;
            margin-top: 12px;
            font-weight: 500;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: rgba(255, 255, 255, 0.4);
        }

        .empty-state-icon {
            font-size: 56px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 15px;
            font-weight: 500;
        }

        /* Efectos de partículas decorativas */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 24px;
                padding: 0 20px;
            }

            .nav-buttons {
                justify-content: center;
                width: 100%;
            }

            .nav-btn {
                flex: 1;
                justify-content: center;
                min-width: 140px;
            }

            .welcome-banner {
                padding: 40px 24px;
            }

            .welcome-banner h2 {
                font-size: 32px;
            }

            .welcome-banner p {
                font-size: 16px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .recent-grid {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding: 30px 20px;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            animation: fadeInUp 0.6s ease backwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(15, 15, 30, 0.5);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #8b5cf6 0%, #6366f1 100%);
        }
    </style>
</head>
<body>
    <!-- Header con Navegación -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">🎓</div>
                <div class="logo-text">
                    <h1>Sistema de Gestión Escolar</h1>
                    <p>Administración de Carreras, Grupos y Alumnos</p>
                </div>
            </div>

            <nav class="nav-buttons">
                <a href="configurar_catalogos.php" class="nav-btn">
                    ⚙️ Catálogo Carreras
                </a>
                <a href="registrar_grupo.php" class="nav-btn">
                    📚 Registrar Grupo
                </a>
                <a href="registrar_alumno.php" class="nav-btn primary">
                    ➕ Registrar Alumno
                </a>
                <a href="alumnos_registrados.php" class="nav-btn">
                    👥 Ver Alumnos
                </a>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">
        <!-- Banner de Bienvenida -->
        <section class="welcome-banner">
            <h2>¡Bienvenido al Sistema de Gestión Escolar!</h2>
            <p>Administra eficientemente carreras, grupos y alumnos desde un solo lugar</p>

            <div class="quick-actions">
                <a href="registrar_carrera.php" class="quick-action-btn">
                    📝 Nueva Carrera
                </a>
                <a href="registrar_grupo.php" class="quick-action-btn">
                    📚 Nuevo Grupo
                </a>
                <a href="registrar_alumno.php" class="quick-action-btn">
                    👤 Nuevo Alumno
                </a>
            </div>
        </section>

        <!-- Estadísticas -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-number"><?php echo $total_carreras; ?></div>
                <div class="stat-label">Carreras Registradas</div>
                <div class="stat-sublabel"><?php echo $carreras_activas; ?> activas</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-number"><?php echo $total_grupos; ?></div>
                <div class="stat-label">Grupos Creados</div>
                <div class="stat-sublabel">En todas las carreras</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👨‍🎓</div>
                <div class="stat-number"><?php echo $total_alumnos; ?></div>
                <div class="stat-label">Alumnos Inscritos</div>
                <div class="stat-sublabel">Total en el sistema</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo $total_grupos > 0 ? round($total_alumnos / $total_grupos, 1) : 0; ?></div>
                <div class="stat-label">Promedio por Grupo</div>
                <div class="stat-sublabel">Alumnos por grupo</div>
            </div>
        </section>

        <!-- Últimos Registros -->
        <div class="recent-grid">
            <!-- Últimos Alumnos -->
            <section class="recent-section">
                <h3>
                    <span>👨‍🎓</span>
                    Últimos Alumnos Registrados
                </h3>

                <?php if ($ultimos_alumnos->num_rows > 0): ?>
                    <?php while($alumno = $ultimos_alumnos->fetch_assoc()): ?>
                        <div class="recent-item">
                            <div class="recent-item-header">
                                <?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido_paterno'] . ' ' . $alumno['apellido_materno']); ?>
                            </div>
                            <div class="recent-item-detail">
                                <span class="badge"><?php echo htmlspecialchars($alumno['grupo']); ?></span>
                            </div>
                            <div class="recent-item-detail">
                                📚 <?php echo htmlspecialchars($alumno['carrera']); ?>
                            </div>
                            <div class="recent-item-date">
                                🕒 <?php echo date('d/m/Y H:i', strtotime($alumno['fecha_registro'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>No hay alumnos registrados aún</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Últimos Grupos -->
            <section class="recent-section">
                <h3>
                    <span>📚</span>
                    Últimos Grupos Creados
                </h3>

                <?php if ($ultimos_grupos->num_rows > 0): ?>
                    <?php while($grupo = $ultimos_grupos->fetch_assoc()): ?>
                        <div class="recent-item">
                            <div class="recent-item-header">
                                <span class="badge"><?php echo htmlspecialchars($grupo['grupo']); ?></span>
                            </div>
                            <div class="recent-item-detail">
                                📚 <?php echo htmlspecialchars($grupo['carrera']); ?>
                            </div>
                            <div class="recent-item-detail">
                                🕐 <?php echo htmlspecialchars($grupo['turno']); ?> - Grado: <?php echo htmlspecialchars($grupo['grado']); ?>
                            </div>
                            <div class="recent-item-date">
                                🕒 <?php echo date('d/m/Y H:i', strtotime($grupo['fecha_registro'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>No hay grupos registrados aún</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>