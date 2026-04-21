<?php
include 'conexion.php';
try {
    $stmt = $conexion->query("SELECT 'Conexión OK' AS estado");
    $row = $stmt->fetch();
    echo $row['estado'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}