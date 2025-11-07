<?php
// api/registro.php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  
  if (!$data || empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Usuario y contraseña requeridos']);
    exit;
  }

  $username = trim($data['username']);
  $password = $data['password'];

  // Validaciones
  if (strlen($username) < 3) {
    http_response_code(400);
    echo json_encode(['error' => 'El usuario debe tener al menos 3 caracteres']);
    exit;
  }

  if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
  }

  // Verificar si el usuario ya existe
  $stmt = $conn->prepare('SELECT id_users FROM users WHERE username = ? LIMIT 1');
  if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
    exit;
  }
  
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'El usuario ya existe']);
    exit;
  }

  // Crear el nuevo usuario
  $password_hash = password_hash($password, PASSWORD_BCRYPT);
  $rol = 'user'; // Por defecto, los usuarios registrados son 'user', no 'admin'
  
  $stmt = $conn->prepare('INSERT INTO users (username, password_hash, rol) VALUES (?, ?, ?)');
  if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear el usuario']);
    exit;
  }
  
  $stmt->bind_param('sss', $username, $password_hash, $rol);
  
  if ($stmt->execute()) {
    echo json_encode([
      'success' => true,
      'message' => 'Usuario registrado exitosamente',
      'user_id' => $conn->insert_id
    ]);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar el usuario']);
  }
  exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
?>
