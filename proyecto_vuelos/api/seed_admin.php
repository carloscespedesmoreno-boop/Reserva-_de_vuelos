<?php
// Crear usuario admin por única vez si no existe
require_once __DIR__ . '/db.php';
session_start();
header('Content-Type: application/json');

try {
    // Crear tabla si no existe
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        rol ENUM('admin','user') NOT NULL DEFAULT 'admin',
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Verificar si existe admin
    $res = $conn->query("SELECT COUNT(*) AS c FROM users WHERE username='admin'");
    $row = $res ? $res->fetch_assoc() : ['c' => 0];

    if ((int)$row['c'] === 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, rol) VALUES (?,?, 'admin')");
        $stmt->bind_param('ss', $u, $h);
        $u = 'admin';
        $h = $hash;
        $stmt->execute();
        echo json_encode(['message' => 'Usuario admin creado', 'username' => 'admin', 'password' => 'admin123']);
    } else {
        echo json_encode(['message' => 'Usuario admin ya existe']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: '.$e->getMessage()]);
}
?>