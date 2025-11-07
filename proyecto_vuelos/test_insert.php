<?php
// Test directo de inserción
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'api/db.php';

echo "=== TEST DE INSERCIÓN ===\n\n";

// Verificar base de datos
$result = $conn->query('SELECT DATABASE() as db');
$row = $result->fetch_assoc();
echo "Base de datos activa: " . $row['db'] . "\n";

// Verificar tabla
$result = $conn->query("SHOW TABLES LIKE 'reservas'");
if ($result->num_rows > 0) {
    echo "Tabla 'reservas' existe: SÍ\n";
} else {
    echo "Tabla 'reservas' existe: NO\n";
    exit;
}

// Verificar columnas
$result = $conn->query("DESCRIBE reservas");
echo "\nColumnas de la tabla reservas:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Intentar inserción
echo "\n=== PROBANDO INSERCIÓN ===\n";
$sql = "INSERT INTO reservas (nombre_pasajero, documento_pasajero, origen, destino, fecha_ida, fecha_vuelta, personas) 
        VALUES ('Test Usuario', '12345', 'Bogota', 'Cali', '2025-11-08', '2025-11-10', 2)";

echo "SQL: $sql\n\n";

if ($conn->query($sql)) {
    echo "✓ INSERCIÓN EXITOSA\n";
    echo "ID insertado: " . $conn->insert_id . "\n";
    echo "Filas afectadas: " . $conn->affected_rows . "\n";
} else {
    echo "✗ ERROR EN INSERCIÓN\n";
    echo "Error: " . $conn->error . "\n";
    echo "Errno: " . $conn->errno . "\n";
}

// Verificar si se guardó
$result = $conn->query("SELECT COUNT(*) as total FROM reservas");
$row = $result->fetch_assoc();
echo "\nTotal de reservas en la tabla: " . $row['total'] . "\n";
?>
