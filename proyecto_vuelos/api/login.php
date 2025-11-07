<?php
// api/login.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  // Devuelve información de sesión
  if (isset($_SESSION['user_id'])) {
    echo json_encode([
      'loggedIn' => true,
      'username' => $_SESSION['username'] ?? null,
      'rol' => $_SESSION['rol'] ?? null,
    ]);
  } else {
    echo json_encode(['loggedIn' => false]);
  }
  exit;
}

if ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  if (!$data || empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Usuario y contraseña requeridos']);
    exit;
  }

  $username = $conn->real_escape_string($data['username']);
  $password = $data['password'];

  // Buscar usuario
  $stmt = $conn->prepare('SELECT id_users, username, password_hash, rol FROM users WHERE username = ? LIMIT 1');
  if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
    exit;
  }
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password_hash'])) {
      $_SESSION['user_id'] = (int)$row['id_users'];
      $_SESSION['username'] = $row['username'];
      $_SESSION['rol'] = $row['rol'];
      echo json_encode(['success' => true]);
      exit;
    }
  }
  http_response_code(401);
  echo json_encode(['error' => 'Credenciales inválidas']);
  exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>
