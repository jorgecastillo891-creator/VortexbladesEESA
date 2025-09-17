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

// Verificar si se recibió el ID de la alerta
if (!isset($_POST['alerta_id'])) {
    http_response_code(400); // Solicitud inválida
    echo "ID de alerta no especificado.";
    exit;
}

$alertaId = $_POST['alerta_id'];

// Actualizar el estado de la alerta a "completado"
$query = "UPDATE alertas SET estado = 'completado' WHERE id = :alerta_id";
$stmt = $pdo->prepare($query);
$result = $stmt->execute([':alerta_id' => $alertaId]);

if ($result) {
    echo "Tarea completada con éxito.";
} else {
    http_response_code(500); // Error interno del servidor
    echo "Hubo un problema al completar la tarea.";
}
?>