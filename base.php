<?php
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = ($scriptName === '/' || $scriptName === '\\') ? '' : $scriptName;

define('BASE_PATH', $basePath);
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH);

function url($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}
function redirect($path = '') {
    header('Location: ' . url($path));
    exit;
}
?>