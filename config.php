<?php
// config.php

// Datos de conexión
define('DB_HOST', 'localhost');   // Servidor (XAMPP/WAMP/Laragon)
define('DB_USER', 'root');        // Usuario por defecto
define('DB_PASS', '');            // Contraseña (vacía en local)
define('DB_NAME', 'escuela');     // Nombre de tu base de datos

/**
 * Obtiene la conexión a la base de datos
 * @return mysqli
 */
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die('Error de conexión: ' . $conn->connect_error);
    }

    // Charset recomendado
    $conn->set_charset('utf8mb4');

    return $conn;
}
