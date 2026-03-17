<?php
// Recogemos las variables inyectadas por Docker
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

try {
    // Código más seguro contra inyecciones SQL que he encontrado 
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $conexion = new PDO($dsn, $username, $password, $opciones);
    // echo "¡Conexión exitosa a Heimdall con Administrador4!";

} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}
?>