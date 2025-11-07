<?php
// mis_reservas.php
// NOTA: Como las reservas ya no tienen user_id, este endpoint devuelve array vacío
// Las reservas se consultan por documento_pasajero en consulta_reservas.php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'No autenticado']);
  exit;
}

// Como no hay user_id en reservas, devolvemos array vacío
// Los usuarios deben usar consulta_reservas.php con su documento
echo json_encode([]);

