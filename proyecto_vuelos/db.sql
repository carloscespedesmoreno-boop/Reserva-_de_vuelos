-- Crear base de datos y usarla
DROP DATABASE IF EXISTS reservas_vuelos;
CREATE DATABASE reservas_vuelos;
USE reservas_vuelos;

-- Tabla de usuarios para login/admin
CREATE TABLE users (
  id_users INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('admin','user') NOT NULL DEFAULT 'user',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de reservas (sin user_id, independiente de usuarios del sistema)
CREATE TABLE reservas (
  id_reservas INT AUTO_INCREMENT PRIMARY KEY,
  nombre_pasajero VARCHAR(200) NOT NULL,
  documento_pasajero VARCHAR(50) NOT NULL,
  origen VARCHAR(100) NOT NULL,
  destino VARCHAR(100) NOT NULL,
  fecha_ida DATE NOT NULL,
  fecha_vuelta DATE NULL,
  personas INT NOT NULL,
  estado ENUM('pendiente','aprobada','cancelada') DEFAULT 'pendiente',
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_documento_pasajero (documento_pasajero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nota: Los pasajeros que hacen reservas NO son usuarios del sistema
-- Solo el admin y usuarios registrados entran al panel de control