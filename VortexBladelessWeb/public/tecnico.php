<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado y tiene rol de técnico
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tecnico') {
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

// Obtener alertas no resueltas con detalles de las turbinas
$query = "SELECT 
            alertas.id AS alerta_id,
            alertas.id_turbina,
            turbinas.ubicacion,
            turbinas.vibracion,
            turbinas.temperatura,
            turbinas.estado AS estado_turbina,
            alertas.tipo_alerta,
            alertas.fecha_alerta,
            alertas.estado AS estado_alerta
          FROM alertas
          JOIN turbinas ON alertas.id_turbina = turbinas.id
          WHERE alertas.estado = 'pendiente'
          ORDER BY alertas.id ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de operarios
$queryOperarios = "SELECT id, nombre FROM usuarios WHERE rol = 'operario'";
$stmtOperarios = $pdo->prepare($queryOperarios);
$stmtOperarios->execute();
$operarios = $stmtOperarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Técnico</title>
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
            color: #fff; /* Color blanco por defecto */
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

        .panel h3 {
            color: #333; /* Color negro para los títulos */
        }

        .table {
            color: #333; /* Color negro para la tabla */
            table-layout: auto; /* Hacer que la tabla ajuste automáticamente el ancho */
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
            color: #333 !important; /* Texto negro en todos los controles */
        }

        .btn-analizar {
            background-color: #28a745; /* Verde */
            color: #fff;
        }

        th, td {
            text-align: center; /* Centrar el contenido */
            vertical-align: middle; /* Centrar verticalmente */
            word-wrap: break-word; /* Ajustar texto */
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">VORTEX BLADELESS: PANEL DEL TÉCNICO</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- Botón de análisis -->
    <div class="container mt-5 d-flex justify-content-start">
        <button class="btn btn-analizar" onclick="analizarSistema()">Activar sistema de monitoreo de turbinas</button>
    </div>

    <!-- Tabla de alertas -->
    <div class="container mt-5 panel">
        <h3 class="text-center">Lista de alertas</h3>
        <table id="tablaAlertas" class="display table table-bordered">
            <thead>
                <tr>
                    <th>ID alerta</th>
                    <th>ID turbina</th>
                    <th>Ubicación</th>
                    <th>Vibración</th>
                    <th>Temperatura</th>
                    <th>Estado turbina</th>
                    <th>Tipo de alerta</th>
                    <th>Fecha</th>
                    <th>Estado alerta</th>
                    <th>Asignar operarios</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alertas as $alerta): ?>
                    <tr>
                        <td><?= htmlspecialchars($alerta['alerta_id']) ?></td>
                        <td><?= htmlspecialchars($alerta['id_turbina']) ?></td>
                        <td><?= htmlspecialchars($alerta['ubicacion']) ?></td>
                        <td><?= htmlspecialchars($alerta['vibracion']) ?></td>
                        <td><?= htmlspecialchars($alerta['temperatura']) ?></td>
                        <td><?= htmlspecialchars($alerta['estado_turbina']) ?></td>
                        <td><?= htmlspecialchars($alerta['tipo_alerta']) ?></td>
                        <td><?= htmlspecialchars($alerta['fecha_alerta']) ?></td>
                        <td><?= htmlspecialchars($alerta['estado_alerta']) ?></td>
                        <td>
                            <select class="form-select operario" data-alerta-id="<?= $alerta['alerta_id'] ?>" data-operario="1">
                                <option value="">Operario 1</option>
                                <?php foreach ($operarios as $operario): ?>
                                    <option value="<?= $operario['id'] ?>"><?= htmlspecialchars($operario['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="form-select operario" data-alerta-id="<?= $alerta['alerta_id'] ?>" data-operario="2">
                                <option value="">Operario 2</option>
                                <?php foreach ($operarios as $operario): ?>
                                    <option value="<?= $operario['id'] ?>"><?= htmlspecialchars($operario['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-success realizar-mantenimiento" data-alerta-id="<?= $alerta['alerta_id'] ?>">Realizar mantenimiento</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function analizarSistema() {
            fetch('sistemamonitoreo.php')
                .then(response => {
                    if (response.ok) {
                        alert('Sistema analizado y datos actualizados.');
                        location.reload();
                    } else {
                        alert('Hubo un problema al analizar el sistema.');
                    }
                });
        }

        document.querySelectorAll('.realizar-mantenimiento').forEach(button => {
            button.addEventListener('click', () => {
                const alertaId = button.getAttribute('data-alerta-id');
                const operario1 = document.querySelector(`.operario[data-alerta-id="${alertaId}"][data-operario="1"]`).value;
                const operario2 = document.querySelector(`.operario[data-alerta-id="${alertaId}"][data-operario="2"]`).value;

                if (!operario1 && !operario2) {
                    alert('Debe asignar al menos un operario.');
                    return;
                }

                fetch('realizar_mantenimiento.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        alerta_id: alertaId,
                        operario1: operario1,
                        operario2: operario2
                    })
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                })
                .catch(error => console.error('Error:', error));
            });
        });

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