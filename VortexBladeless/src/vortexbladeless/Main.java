package vortexbladeless;

import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

public class Main {
    public static void main(String[] args) {
        // Inicializar el sistema de monitoreo.
        System.out.println("Iniciando el sistema de monitoreo de turbinas...");

        // Crear una lista de turbinas para simular
        List<Turbina> turbinas = new ArrayList<>();
        turbinas.add(new Turbina(1, "Lima, Perú", new Date()));
        turbinas.add(new Turbina(2, "Cusco, Perú", new Date()));
        turbinas.add(new Turbina(3, "Arequipa, Perú", new Date()));
        turbinas.add(new Turbina(4, "Trujillo, Perú", new Date()));
        turbinas.add(new Turbina(5, "Piura, Perú", new Date()));

        // Establecer la conexión a la base de datos
        Connection conexion = null;
        try {
            conexion = BaseDatos.conectar();
            System.out.println("¡Conexión establecida correctamente!");

            // Parte 1: Monitorear y registrar turbinas
        for (Turbina turbina : turbinas) {
            System.out.println("\nMonitoreando la turbina ID = " + turbina.getId() + ", Ubicación = " + turbina.getUbicacion());

            // Simular la recolección de datos de la turbina
            turbina.recolectarDatos();
            System.out.println("Datos recolectados:");
            System.out.println("Vibración: " + turbina.getVibracion() + " | Temperatura: " + turbina.getTemperatura());

            // Registrar las turbinas en la base de datos (solo si no están ya registradas)
            BaseDatos.registrarTurbina(turbina);

            // Verificar si hay una anomalía en la turbina
            if (turbina.detectarAnomalia()) {
                // Simulación del ID del técnico que genera la alerta
                int idTecnico = 101; // Puedes reemplazarlo por otro valor si tienes más técnicos

                // Crear una alerta si se detecta una anomalía
                Alerta alerta = new Alerta(turbina.getId(), "Anomalía detectada en turbina ID: " + turbina.getId(), idTecnico);
                BaseDatos.registrarAlerta(alerta);
                System.out.println("Alerta registrada para la turbina ID: " + turbina.getId());
                System.out.println(alerta.mostrarDetalle());
            } else {
                System.out.println("La turbina " + turbina.getId() + " está funcionando correctamente.");
            }
        }

            // Parte 2: Consultar turbinas almacenadas
            System.out.println("\nConsultando turbinas registradas:");
            BaseDatos.obtenerTurbinas();

            // Parte 3: Consultar alertas almacenadas
            System.out.println("\nConsultando alertas registradas:");
            BaseDatos.obtenerAlertas();

        } catch (SQLException e) {
            System.err.println("Error de conexión: " + e.getMessage());
        } finally {
            // Cerrar la conexión en el bloque finally para asegurar que siempre se cierre
            if (conexion != null) {
                BaseDatos.cerrarConexion(conexion);
            }
        }

        System.out.println("\nSistema de monitoreo finalizado.");
    }
}
