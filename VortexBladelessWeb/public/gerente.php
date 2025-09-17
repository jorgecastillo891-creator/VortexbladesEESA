<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y tiene rol de gerente
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'gerente') {
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

// Consultar el total de alertas
$queryTotalAlertas = "SELECT COUNT(*) AS total_alertas FROM alertas";
$totalAlertas = $pdo->query($queryTotalAlertas)->fetch(PDO::FETCH_ASSOC)['total_alertas'] ?? 0;

// Consultar el total de alertas atendidas
$queryAlertasAtendidas = "SELECT COUNT(*) AS alertas_atendidas FROM alertas WHERE estado = 'atendido'";
$alertasAtendidas = $pdo->query($queryAlertasAtendidas)->fetch(PDO::FETCH_ASSOC)['alertas_atendidas'] ?? 0;

// Consultar el total de alertas pendientes
$queryAlertasPendientes = "SELECT COUNT(*) AS alertas_pendientes FROM alertas WHERE estado = 'pendiente'";
$alertasPendientes = $pdo->query($queryAlertasPendientes)->fetch(PDO::FETCH_ASSOC)['alertas_pendientes'] ?? 0;

// Consultar el total de alertas completadas
$queryAlertasCompletadas = "SELECT COUNT(*) AS alertas_completadas FROM alertas WHERE estado = 'completado'";
$alertasCompletadas = $pdo->query($queryAlertasCompletadas)->fetch(PDO::FETCH_ASSOC)['alertas_completadas'] ?? 0;

// Consultar detalles de las alertas
$queryDetalles = "
    SELECT 
        alertas.id AS alerta_id,
        alertas.id_turbina,
        alertas.tipo_alerta,
        alertas.estado,
        alertas.fecha_alerta,
        turbinas.ubicacion,
        u1.nombre AS operario1,
        u2.nombre AS operario2
    FROM alertas
    LEFT JOIN turbinas ON alertas.id_turbina = turbinas.id
    LEFT JOIN usuarios u1 ON alertas.id_operario1 = u1.id
    LEFT JOIN usuarios u2 ON alertas.id_operario2 = u2.id
";
$stmtDetalles = $pdo->query($queryDetalles);
$detallesAlertas = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC) ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del gerente</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-image: url('imagenes/bgtodos.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #fff; /* Cambia el color del texto por defecto */
        }

        .panel {
            background-color: rgba(255, 255, 255, 0.8); /* Fondo semitransparente */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Sombra para destacar */
            margin-bottom: 20px;
        }

        .panel h1, .panel h3, .panel h5 {
            color: #333; /* Cambia el color de los títulos */
        }

        .table {
            color: #333; /* Color negro para la tabla */
        }

        .card-custom-total {
            background-color: #007bff; /* Azul similar al de Bootstrap */
            color: #333; /* Texto negro */
        }

        .card-custom-pending {
            background-color: #FFCE56; /* Amarillo */
            color: #333; /* Texto negro */
        }

        .card-custom-attended {
            background-color: #36A2EB; /* Azul */
            color: #333; /* Texto blanco */
        }

        .card-custom-completed {
            background-color: #4BC0C0; /* Verde agua */
            color: #333; /* Texto blanco */
        }
        .dataTables_wrapper {
            color: #333; /* Color negro para el texto general de DataTables */
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            color: #333; /* Color negro para las etiquetas de 'Buscar' y 'Mostrar' */
        }

        .dataTables_wrapper .dataTables_info {
            color: #333; /* Color negro para la información sobre los registros */
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #333; /* Color negro para los botones de paginación */
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">VORTEX BLADELESS: PANEL DEL GERENTE</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- Panel unificado -->
    <div class="container mt-5 panel">
        <div class="row">
            <!-- Cards del dashboard -->
            <div class="col-md-6">
                <h3 class="text-center">Dashboard</h3>
                <div class="card card-custom-total mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total de alertas</h5>
                        <p class="card-text fs-1"><?= htmlspecialchars($totalAlertas) ?></p>
                    </div>
                </div>
                <div class="card card-custom-pending mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Alertas pendientes</h5>
                        <p class="card-text fs-1"><?= htmlspecialchars($alertasPendientes) ?></p>
                    </div>
                </div>
                <div class="card card-custom-attended mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Alertas atendidas</h5>
                        <p class="card-text fs-1"><?= htmlspecialchars($alertasAtendidas) ?></p>
                    </div>
                </div>
                <div class="card card-custom-completed mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Alertas completadas</h5>
                        <p class="card-text fs-1"><?= htmlspecialchars($alertasCompletadas) ?></p>
                    </div>
                </div>
            </div>

            <!-- Gráfico de alertas -->
            <div class="col-md-6 d-flex flex-column align-items-center">
                <h3 class="text-center">Distribución de alertas</h3>
                <div style="max-width: 90%; margin: 0 auto;">
                    <canvas id="alertasChart" width="600" height="600"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla detallada -->
    <div class="container mt-5 panel">
        <h3 class="text-center">Detalles de alertas</h3>
        <table id="tablaAlertas" class="display table table-bordered">
            <thead>
                <tr>
                    <th>ID Alerta</th>
                    <th>ID Turbina</th>
                    <th>Ubicación</th>
                    <th>Tipo de alerta</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Operario 1</th>
                    <th>Operario 2</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detallesAlertas as $alerta): ?>
                    <tr>
                        <td><?= htmlspecialchars($alerta['alerta_id']) ?></td>
                        <td><?= htmlspecialchars($alerta['id_turbina']) ?></td>
                        <td><?= htmlspecialchars($alerta['ubicacion']) ?></td>
                        <td><?= htmlspecialchars($alerta['tipo_alerta']) ?></td>
                        <td><?= htmlspecialchars($alerta['estado']) ?></td>
                        <td><?= htmlspecialchars($alerta['fecha_alerta']) ?></td>
                        <td><?= htmlspecialchars($alerta['operario1'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($alerta['operario2'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Gráfico de distribución de alertas
        fetch('datos_alertas.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('alertasChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendientes', 'Atendidas', 'Completadas'],
                        datasets: [{
                            data: [data.pendientes, data.resueltas, data.completadas],
                            backgroundColor: ['#FFCE56', '#36A2EB', '#4BC0C0'], // Colores consistentes con las tarjetas
                            hoverBackgroundColor: ['#FFCE56', '#36A2EB', '#4BC0C0']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' }
                        }
                    }
                });
            })
            .catch(error => console.error('Error al obtener datos para el gráfico:', error));

        // Inicializar DataTables
        $(document).ready(function() {
            $('#tablaAlertas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                }
            });
        });
    </script>
</body>
</html>
