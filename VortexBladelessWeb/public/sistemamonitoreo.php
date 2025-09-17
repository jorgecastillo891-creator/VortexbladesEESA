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

// Ruta completa al ejecutable de Java y al archivo .jar
$javaPath = 'C:\\Program Files\\Java\\jdk-23\\bin\\java.exe'; 
$jarFile = 'C:\\xampp\\htdocs\\VortexBladelessWeb\\java\\VortexBladeless.jar';

// Ejecutar el programa Java
$command = "\"$javaPath\" -jar \"$jarFile\"";
exec($command, $output, $returnVar);

if ($returnVar !== 0) {
    error_log("Error al ejecutar: $command");
    error_log("Código de retorno: $returnVar");
    error_log("Salida: " . implode("\n", $output));
    http_response_code(500);
    echo "Error al ejecutar el sistema de monitoreo. Revisa los logs para más detalles.";
    exit;
}

// Simular la inserción de alertas (si el programa Java no actualiza la base directamente)
foreach ($output as $line) {
    $data = explode('|', $line);
    if (count($data) === 2) {
        $idTurbina = $data[0];
        $tipoAlerta = $data[1];

        // Insertar en la base de datos
        $query = "INSERT INTO alertas (id_turbina, tipo_alerta, fecha_alerta, estado) 
                  VALUES (:id_turbina, :tipo_alerta, NOW(), 'pendiente')";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':id_turbina' => $idTurbina,
            ':tipo_alerta' => $tipoAlerta
        ]);
    }
}

// Respuesta al navegador
http_response_code(200);
echo "Sistema analizado correctamente.";
?>