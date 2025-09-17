<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Obtener datos enviados por AJAX
$alertaId = $_POST['alerta_id'] ?? null;
$operario1 = $_POST['operario1'] ?? null;
$operario2 = $_POST['operario2'] ?? null;

// Validación de datos
if (!$alertaId) {
    http_response_code(400);
    echo "ID de alerta no especificado.";
    exit;
}

if (!$operario1 && !$operario2) {
    http_response_code(400);
    echo "Debe asignar al menos un operario.";
    exit;
}

// Crear los campos para asignar operarios
$asignaciones = [];
$asignaciones[':operario1'] = $operario1 ?: null;
$asignaciones[':operario2'] = $operario2 ?: null;
$asignaciones[':alerta_id'] = $alertaId;

// Actualizar la alerta
$query = "UPDATE alertas 
          SET id_operario1 = :operario1, id_operario2 = :operario2, estado = 'atendido'
          WHERE id = :alerta_id";
$stmt = $pdo->prepare($query);

try {
    $stmt->execute($asignaciones);

    if ($stmt->rowCount() > 0) {
        echo "Mantenimiento realizado con éxito. La alerta ha sido atendida.";
    } else {
        http_response_code(400);
        echo "No se encontró la alerta especificada o ya fue actualizada.";
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Error al actualizar la alerta: " . $e->getMessage();
}
?>