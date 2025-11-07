<?php
header("Content-Type: application/json");
session_start();
if (!isset($_SESSION['user_id']) || (($_SESSION['rol'] ?? '') !== 'admin')) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}
require_once "db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'DELETE') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID de reserva requerido"]);
        exit;
    }

    $id = (int) $_GET['id'];

    // Usar la columna correcta de clave primaria
    $sql = "DELETE FROM reservas WHERE id_reservas = $id";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "Reserva eliminada correctamente"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al eliminar la reserva"]);
    }
    exit;
}

// Método no permitido
http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
?>