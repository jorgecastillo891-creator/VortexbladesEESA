<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y tiene rol de operario
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'operario') {
    header('Location: /VortexBladelessWeb/public/login.php'); // Redirigir a la página de inicio de sesión si no está autenticado
    exit;
}

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

// Obtener el ID del operario autenticado desde la sesión
$operarioId = $_SESSION['user_id'];

// Obtener las alertas asignadas al operario
$query = "SELECT 
            alertas.id AS alerta_id,
            alertas.id_turbina,
            turbinas.ubicacion,
            alertas.tipo_alerta,
            alertas.fecha_alerta,
            alertas.estado
          FROM alertas
          JOIN turbinas ON alertas.id_turbina = turbinas.id
          WHERE (alertas.id_operario1 = :operario_id OR alertas.id_operario2 = :operario_id)
          ORDER BY alertas.fecha_alerta ASC";
$stmt = $pdo->prepare($query);
$stmt->execute([':operario_id' => $operarioId]);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Operario</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <style>
        body {
            background-image: url('imagenes/bgtodos.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #fff; 
        }

        .panel {
            background-color: rgba(255, 255, 255, 0.8); /* Fondo semitransparente */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Sombra para destacar */
            margin-bottom: 20px;
            max-width: 90%; /* Centrar el panel */
            margin-left: auto;
            margin-right: auto;
        }

        .panel h1, .panel h3 {
            color: #333; /* Color negro para los títulos */
        }

        .table {
            color: #333; /* Color negro para la tabla */
            table-layout: auto; /* Ajustar automáticamente el ancho */
            width: 100%; /* Asegurar que la tabla no salga del panel */
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            color: #333; /* Texto negro en campos */
            background-color: #fff; /* Fondo blanco */
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length label {
            color: #333 !important; /* Texto negro */
        }

        th, td {
            text-align: center; /* Centrar el contenido */
            vertical-align: middle; /* Centrar verticalmente */
            word-wrap: break-word; /* Ajustar texto */
        }

        .btn-completar {
            background-color: #28a745; /* Verde */
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">VORTEX BLADELESS: PANEL DEL OPERARIO</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- Panel de tareas -->
    <div class="container mt-5 panel">
        <h3 class="text-center mb-4">Tareas asignadas</h3>
        <table id="tablaAlertas" class="display table table-bordered">
            <thead>
                <tr>
                    <th>ID alerta</th>
                    <th>ID turbina</th>
                    <th>Ubicación</th>
                    <th>Tipo de alerta</th>
                    <th>Fecha de asignación</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tareas)): ?>
                    <!-- Fila vacía que coincide con el número exacto de columnas -->
                    <tr>
                        <td colspan="7" class="text-center">No hay tareas asignadas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tareas as $tarea): ?>
                        <tr>
                            <td><?= htmlspecialchars($tarea['alerta_id']) ?></td>
                            <td><?= htmlspecialchars($tarea['id_turbina']) ?></td>
                            <td><?= htmlspecialchars($tarea['ubicacion']) ?></td>
                            <td><?= htmlspecialchars($tarea['tipo_alerta']) ?></td>
                            <td><?= htmlspecialchars($tarea['fecha_alerta']) ?></td>
                            <td><?= htmlspecialchars($tarea['estado']) ?></td>
                            <td>
                                <?php if ($tarea['estado'] === 'atendido'): ?>
                                    <button class="btn btn-success completar-tarea" data-id="<?= $tarea['alerta_id'] ?>">Completar tarea</button>
                                <?php else: ?>
                                    <span class="text-muted">Completada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

    <script>
        document.querySelectorAll('.completar-tarea').forEach(button => {
            button.addEventListener('click', () => {
                const alertaId = button.getAttribute('data-id');

                // Confirmar la acción
                if (!confirm('¿Estás seguro de que deseas marcar esta tarea como completada?')) {
                    return;
                }

                // Enviar solicitud para completar la tarea
                fetch('completar_tarea.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ 'alerta_id': alertaId })
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload(); // Refrescar la página
                })
                .catch(error => console.error('Error:', error));
            });
        });

        $(document).ready(function() {
            $('#tablaAlertas').DataTable({
                autoWidth: true, // Ajustar el ancho automáticamente
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
</body>
</html>