<?php
// reservas.php
session_start();
header("Content-Type: application/json");
require_once "db.php";

// Logging simple a archivo para diagnosticar inserciones
function log_event($msg) {
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    $file = $dir . DIRECTORY_SEPARATOR . 'reservas.log';
    $ts = date('Y-m-d H:i:s');
    @file_put_contents($file, "[$ts] $msg\n", FILE_APPEND);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Obtener todas las reservas
    $sql = "SELECT * FROM reservas ORDER BY fecha_creacion DESC";
    $result = $conn->query($sql);
    $reservas = [];

    while ($row = $result->fetch_assoc()) {
        $reservas[] = $row;
    }

    echo json_encode($reservas);
    exit;
}

if ($method === 'POST') {
    // Crear nueva reserva
    $data = json_decode(file_get_contents("php://input"), true);

    // Registrar payload recibido
    log_event('POST /reservas payload: ' . json_encode($data));
    log_event('PASO 1: Validando campos requeridos...');

    if (!$data || empty($data['nombre_pasajero']) || empty($data['documento_pasajero']) || empty($data['origen']) || empty($data['destino']) || empty($data['fecha_ida']) || empty($data['personas'])) {
        log_event('ERROR: Datos incompletos. Data: ' . json_encode($data));
        http_response_code(400);
        echo json_encode(["error" => "Datos incompletos"]);
        exit;
    }
    
    log_event('PASO 2: Campos requeridos OK, validando nombre...');

    log_event('PASO 2: Campos requeridos OK, validando nombre...');

    // Validar nombre del pasajero (solo letras y espacios)
    if (!preg_match('/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/', $data['nombre_pasajero'])) {
        log_event('ERROR: Nombre pasajero inválido: ' . $data['nombre_pasajero']);
        http_response_code(400);
        echo json_encode(["error" => "El nombre del pasajero solo puede contener letras"]);
        exit;
    }
    
    log_event('PASO 3: Nombre OK, validando documento...');

    log_event('PASO 3: Nombre OK, validando documento...');

    // Validar documento (mínimo 5 caracteres)
    if (strlen($data['documento_pasajero']) < 5) {
        log_event('ERROR: Documento pasajero muy corto: ' . $data['documento_pasajero']);
        http_response_code(400);
        echo json_encode(["error" => "El documento debe tener al menos 5 caracteres"]);
        exit;
    }
    
    log_event('PASO 4: Documento OK, preparando datos...');

    $nombre_pasajero = $conn->real_escape_string($data['nombre_pasajero']);
    $documento_pasajero = $conn->real_escape_string($data['documento_pasajero']);
    
    log_event('PASO 5: Verificando límite de reservas...');

    log_event('PASO 5: Verificando límite de reservas...');

    // Verificar límite de 2 reservas activas por documento
    $sql_check = "SELECT COUNT(*) as total FROM reservas WHERE documento_pasajero = '$documento_pasajero' AND estado != 'cancelada'";
    $result_check = $conn->query($sql_check);
    $row_check = $result_check->fetch_assoc();
    
    log_event('PASO 6: Reservas activas encontradas: ' . $row_check['total']);
    
    if ($row_check['total'] >= 2) {
        log_event('ERROR: Límite de reservas activas alcanzado para documento: ' . $documento_pasajero);
        http_response_code(400);
        echo json_encode([
            "error" => "⚠️ Límite alcanzado: Ya tienes " . $row_check['total'] . " reservas activas. Solo puedes tener máximo 2 reservas activas a la vez. Para crear una nueva reserva, primero debes esperar a que una sea cancelada o completada."
        ]);
        exit;
    }
    
    log_event('PASO 7: Límite OK, preparando INSERT...');

    $origen = $conn->real_escape_string($data['origen']);
    $destino = $conn->real_escape_string($data['destino']);
    $fecha_ida = $conn->real_escape_string($data['fecha_ida']);
    $fecha_vuelta = (isset($data['fecha_vuelta']) && $data['fecha_vuelta'] && strtolower($data['fecha_vuelta']) !== 'null')
        ? "'" . $conn->real_escape_string($data['fecha_vuelta']) . "'"
        : "NULL";
    $personas = (int) $data['personas'];

    $sql = "INSERT INTO reservas (nombre_pasajero, documento_pasajero, origen, destino, fecha_ida, fecha_vuelta, personas) 
        VALUES ('$nombre_pasajero', '$documento_pasajero', '$origen', '$destino', '$fecha_ida', $fecha_vuelta, $personas)";
    
    // Registrar base de datos en uso
    $rdb = $conn->query('SELECT DATABASE() as db');
    if ($rdb && ($rw = $rdb->fetch_assoc())) {
        log_event('DB: database=' . $rw['db']);
    }
    log_event('SQL: ' . $sql);

    $result = $conn->query($sql);
    log_event('Query result: ' . var_export($result, true));
    if ($result) {
        $id = $conn->insert_id;
        log_event('INSERT OK id=' . $id . ' affected=' . $conn->affected_rows);
        echo json_encode(["message" => "Reserva creada con éxito", "id" => $id]);
    } else {
        $err = $conn->error;
        log_event('INSERT ERROR: ' . $err . ' | SQLSTATE: ' . $conn->sqlstate);
        log_event('Full SQL: ' . $sql);
        http_response_code(500);
        echo json_encode(["error" => "Error al crear la reserva. Detalle: $err"]);
    }
    exit;
}

// Método no permitido
http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
?>
