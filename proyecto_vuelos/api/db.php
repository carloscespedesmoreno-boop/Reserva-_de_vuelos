<?php
// db.php
$host = "localhost";
$user = "root";        // Cambia si tu usuario MySQL es distinto
$pass = "";            // Coloca tu contraseña de MySQL
$db   = "reservas_vuelos";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");
$conn->autocommit(true);
?>
    