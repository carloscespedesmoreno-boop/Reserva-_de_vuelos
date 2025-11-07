<?php
// reserva_id.php
header("Content-Type: application/json");
session_start();
// Verificación de rol admin
if (!isset($_SESSION['user_id']) || (($_SESSION['rol'] ?? '') !== 'admin')) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}
require_once "db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'PUT') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID de reserva requerido"]);
        exit;
    }

    $id = (int) $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['estado'])) {
        http_response_code(400);
        echo json_encode(["error" => "Estado requerido"]);
        exit;
    }

    $estado = $conn->real_escape_string($data['estado']);

    if (!in_array($estado, ['pendiente', 'aprobada', 'cancelada'])) {
        http_response_code(400);
        echo json_encode(["error" => "Estado no válido"]);
        exit;
    }

    // Usar la columna correcta de clave primaria
    $sql = "UPDATE reservas SET estado='$estado' WHERE id_reservas = $id";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "Estado actualizado correctamente"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al actualizar estado"]);
    }
    exit;
}

// Método no permitido
http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
?>
