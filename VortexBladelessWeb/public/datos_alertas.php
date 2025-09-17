<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'vortex_turbinas';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Consultar el total de alertas pendientes y resueltas
$queryPendientes = "SELECT COUNT(*) AS total FROM alertas WHERE estado = 'pendiente'";
$queryResueltas = "SELECT COUNT(*) AS total FROM alertas WHERE estado = 'atendido'";

$pendientes = $pdo->query($queryPendientes)->fetch(PDO::FETCH_ASSOC)['total'];
$resueltas = $pdo->query($queryResueltas)->fetch(PDO::FETCH_ASSOC)['total'];

// Consultar el total de alertas completadas
$queryCompletadas = "SELECT COUNT(*) AS total FROM alertas WHERE estado = 'completado'";
$completadas = $pdo->query($queryCompletadas)->fetch(PDO::FETCH_ASSOC)['total'];

// Enviar datos como JSON
echo json_encode([
    'pendientes' => (int)$pendientes,
    'resueltas' => (int)$resueltas,
    'completadas' => (int)$completadas
]);