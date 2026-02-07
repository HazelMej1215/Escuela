<?php
// config.php (DEV)

// 1) Mostrar errores en pantalla (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Guardar errores en un archivo (para verlos aunque la pantalla esté en blanco)
ini_set('log_errors', 1);
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('error_log', $logDir . '/php_errors.log');

// 3) Datos de conexión (ajusta DB_NAME a como la tengas en phpMyAdmin)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'escuela'); // <-- cambia si tu BD tiene otro nombre

// 4) Función para depurar rápido (opcional)
function dd($value) {
    echo "<pre style='background:#111;color:#0f0;padding:12px;border-radius:8px;overflow:auto;'>";
    var_dump($value);
    echo "</pre>";
    exit;
}

// 5) Conexión robusta con excepciones
function getConnection(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (mysqli_sql_exception $e) {
        // Mostrar el error en pantalla y registrarlo
        http_response_code(500);
        echo "<h2 style='font-family:Arial;color:#b00020'>Error de conexión a la base de datos</h2>";
        echo "<p style='font-family:Arial'>Revisa DB_NAME/USER/PASS y que MySQL esté encendido.</p>";
        echo "<pre style='background:#f6f6f6;padding:12px;border-radius:8px;'>";
        echo htmlspecialchars($e->getMessage());
        echo "</pre>";
        exit;
    }
}
