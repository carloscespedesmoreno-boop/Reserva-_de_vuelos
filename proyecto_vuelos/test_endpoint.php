<?php
// Test del endpoint POST
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular request POST
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simular datos JSON
$testData = [
    'nombre_pasajero' => 'Juan Perez',
    'documento_pasajero' => '123456789',
    'origen' => 'Bogota',
    'destino' => 'Cali',
    'fecha_ida' => '2025-11-08',
    'fecha_vuelta' => '2025-11-10',
    'personas' => '2'
];

// Configurar stream input simulado
file_put_contents('php://memory', json_encode($testData));

echo "=== TEST ENDPOINT POST ===\n";
echo "Datos enviados: " . json_encode($testData) . "\n\n";
echo "Resultado del endpoint:\n";
echo "---\n";

// Capturar output
ob_start();
include 'api/reservas.php';
$output = ob_get_clean();

echo $output;
echo "\n---\n";

// Verificar log
if (file_exists('api/logs/reservas.log')) {
    echo "\nÚltimas líneas del log:\n";
    $lines = file('api/logs/reservas.log');
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        echo $line;
    }
}
?>
