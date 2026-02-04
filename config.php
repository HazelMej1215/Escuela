<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'escuela_db');

// Crear conexión
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    return $conn;
}

// Crear base de datos y tablas si no existen
function initDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    // Crear base de datos
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8 COLLATE utf8_general_ci";
    $conn->query($sql);
    
    $conn->select_db(DB_NAME);
    
    // Crear tabla de grupos
    $sql = "CREATE TABLE IF NOT EXISTS grupos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        carrera VARCHAR(100) NOT NULL,
        turno VARCHAR(50) NOT NULL,
        grado VARCHAR(50) NOT NULL,
        grupo VARCHAR(100) NOT NULL UNIQUE,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Crear tabla de alumnos
    $sql = "CREATE TABLE IF NOT EXISTS alumnos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellido_paterno VARCHAR(100) NOT NULL,
        apellido_materno VARCHAR(100) NOT NULL,
        grupo_id INT NOT NULL,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE
    )";
    $conn->query($sql);
    
    $conn->close();
}

// Inicializar la base de datos
initDatabase();
?>