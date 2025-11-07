<?php
// consulta_reservas.php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
  http_response_code(405);
  echo json_encode(['error' => 'Método no permitido']);
  exit;
}

$documento = isset($_GET['documento']) ? trim($_GET['documento']) : '';
if ($documento === '' || strlen($documento) < 5) {
  http_response_code(400);
  echo json_encode(['error' => 'Documento inválido (mínimo 5 caracteres)']);
  exit;
}

$documento_safe = $conn->real_escape_string($documento);
$sql = "SELECT id_reservas, nombre_pasajero, documento_pasajero, origen, destino, fecha_ida, fecha_vuelta, personas, estado, fecha_creacion 
        FROM reservas WHERE documento_pasajero = '$documento_safe' ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);
$reservas = [];
while ($row = $result->fetch_assoc()) {
  $reservas[] = $row;
}

echo json_encode($reservas);
