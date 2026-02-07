<?php
require_once 'config.php';

echo "PHP cargó config ✅<br>";

$conn = getConnection();
echo "Conexión MySQL ✅<br>";

$res = $conn->query("SHOW TABLES");
echo "Tablas encontradas:<br><pre>";
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
echo "</pre>";
