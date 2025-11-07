<?php
header('Content-Type: application/json');
$report = ['ok' => true];
try {
  require_once 'db.php';
  $report['db'] = 'conectada';
  // Base de datos en uso
  if ($rdb = $conn->query('SELECT DATABASE() as db')) {
    if ($row = $rdb->fetch_assoc()) { $report['database'] = $row['db']; }
  }
  // Validar existencia de tablas
  $tables = [];
  $rs = $conn->query("SHOW TABLES");
  while ($row = $rs->fetch_row()) { $tables[] = $row[0]; }
  $report['tablas'] = $tables;
  // Contadores
  $cntUsers = 0; $cntReservas = 0;
  if (in_array('users', $tables)) {
    $r = $conn->query('SELECT COUNT(*) c FROM users');
    $cntUsers = ($r && ($row = $r->fetch_assoc())) ? (int)$row['c'] : 0;
  }
  if (in_array('reservas', $tables)) {
    $r = $conn->query('SELECT COUNT(*) c FROM reservas');
    $cntReservas = ($r && ($row = $r->fetch_assoc())) ? (int)$row['c'] : 0;
  }
  $report['conteos'] = ['users' => $cntUsers, 'reservas' => $cntReservas];
  // Últimas reservas
  if (in_array('reservas', $tables)) {
    $r = $conn->query('SELECT id_reservas, nombre_pasajero, documento_pasajero, estado, fecha_creacion FROM reservas ORDER BY id_reservas DESC LIMIT 3');
    $ult = [];
    if ($r) { while ($row = $r->fetch_assoc()) { $ult[] = $row; } }
    $report['ultimas_reservas'] = $ult;
  }
  echo json_encode($report);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
