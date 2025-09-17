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

// Iniciar sesión
session_start();

$errorMessage = "";

// Verificar si se enviaron credenciales
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Consultar base de datos
    $query = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['contrasena']) {
        // Credenciales correctas
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol'];

        // Redirigir según el rol del usuario
        if ($user['rol'] == 'operario') {
            header('Location: operario.php');
        } elseif ($user['rol'] == 'gerente') {
            header('Location: gerente.php');
        } elseif ($user['rol'] == 'tecnico') {
            header('Location: tecnico.php');
        }
        exit;
    } else {
        // Credenciales incorrectas
        $errorMessage = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('imagenes/bgloginphp.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 130px; /* Posición vertical */
        }

        .container {
            width: 40%; /* Ancho del contenedor principal */
            max-width: 700px; /* Limita el ancho máximo a 700px */
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
    <script>
        // Mostrar ventana emergente si hay un mensaje de error
        function showError(message) {
            if (message) {
                alert(message);
            }
        }
    </script>
</head>
<body onload="showError('<?= htmlspecialchars($errorMessage) ?>')">
    <div class="container">
        <h1 class="text-center">Iniciar sesión</h1>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
        </form>
    </div>
</body>
</html>