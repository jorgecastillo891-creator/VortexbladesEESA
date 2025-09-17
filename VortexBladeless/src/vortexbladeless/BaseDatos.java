package vortexbladeless;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.PreparedStatement;
import java.sql.ResultSet;

public class BaseDatos {

    // URL de conexión a la base de datos (ajustar si es necesario)
    private static final String URL = "jdbc:mysql://localhost:3306/vortex_turbinas"; // Cambia "vortex_turbinas" al nombre de tu base de datos
    private static final String USER = "root";  // Cambia "root" si usas otro usuario
    private static final String PASSWORD = "";  // Deja en blanco si no tienes contraseña, o ingresa la tuya

    // Método para establecer la conexión con la base de datos
    public static Connection conectar() throws SQLException {
        Connection conexion = DriverManager.getConnection(URL, USER, PASSWORD);
        System.out.println("Conexión exitosa a la base de datos.");
        return conexion;
    }

    // Método para cerrar la conexión con la base de datos
    public static void cerrarConexion(Connection conexion) {
        if (conexion != null) {
            try {
                conexion.close();
                System.out.println("Conexión cerrada.");
            } catch (SQLException e) {
                System.err.println("Error al cerrar la conexión: " + e.getMessage());
            }
        }
    }

    // Método para registrar una turbina en la base de datos
    public static void registrarTurbina(Turbina turbina) {
        String queryCheck = "SELECT COUNT(*) FROM turbinas WHERE id = ?";
        String queryInsert = "INSERT INTO turbinas (id, ubicacion, vibracion, temperatura, estado, fecha_instalacion) VALUES (?, ?, ?, ?, ?, ?)";

        try (Connection conexion = conectar();
            PreparedStatement stmtCheck = conexion.prepareStatement(queryCheck);
            PreparedStatement stmtInsert = conexion.prepareStatement(queryInsert)) {

            // Verificar si la turbina ya existe en la base de datos
            stmtCheck.setInt(1, turbina.getId());
            ResultSet rs = stmtCheck.executeQuery();
            rs.next();
            int count = rs.getInt(1);

        if (count == 0) {
            // Si la turbina no existe, insertar la nueva turbina
            stmtInsert.setInt(1, turbina.getId());
            stmtInsert.setString(2, turbina.getUbicacion());
            stmtInsert.setFloat(3, turbina.getVibracion());
            stmtInsert.setFloat(4, turbina.getTemperatura());
            stmtInsert.setString(5, turbina.getEstado());
            stmtInsert.setDate(6, new java.sql.Date(turbina.getFechaInstalacion().getTime()));

            int filasInsertadas = stmtInsert.executeUpdate();
            if (filasInsertadas > 0) {
                System.out.println("Turbina registrada correctamente.");
            }
        } else {
            // Si la turbina ya existe, no la insertamos
            System.out.println("Turbina con ID = " + turbina.getId() + " ya está registrada. No se insertará nuevamente.");
        }

    } catch (SQLException e) {
        System.err.println("Error al registrar la turbina: " + e.getMessage());
    }
}

    // Método para obtener todas las turbinas de la base de datos
    public static void obtenerTurbinas() {
        String query = "SELECT * FROM turbinas";

        try (Connection conexion = conectar();
             PreparedStatement stmt = conexion.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                int id = rs.getInt("id");
                String ubicacion = rs.getString("ubicacion");
                float vibracion = rs.getFloat("vibracion");
                float temperatura = rs.getFloat("temperatura");
                String estado = rs.getString("estado");
                java.sql.Date fechaInstalacion = rs.getDate("fecha_instalacion");

                System.out.println("ID: " + id + ", Ubicación: " + ubicacion + ", Vibración: " + vibracion + ", Temperatura: " + temperatura + ", Estado: " + estado + ", Fecha de Instalación: " + fechaInstalacion);
            }

        } catch (SQLException e) {
            System.err.println("Error al obtener las turbinas: " + e.getMessage());
        }
    }

    // Método para registrar una alerta en la base de datos
    public static void registrarAlerta(Alerta alerta) {
        String query = "INSERT INTO alertas (id_turbina, tipo_alerta, id_tecnico, fecha_alerta) VALUES (?, ?, ?, ?)";

        try (Connection conexion = conectar();
             PreparedStatement stmt = conexion.prepareStatement(query)) {

            // Asignar los valores al PreparedStatement
            stmt.setInt(1, alerta.getIdTurbina());
            stmt.setString(2, alerta.getTipoAlerta());
            stmt.setInt(3, alerta.getIdTecnico());
            stmt.setTimestamp(4, new java.sql.Timestamp(alerta.getFechaAlerta().getTime()));

            // Ejecutar la consulta
            int filasInsertadas = stmt.executeUpdate();
            if (filasInsertadas > 0) {
                System.out.println("Alerta registrada correctamente.");
            }

        } catch (SQLException e) {
            System.err.println("Error al registrar la alerta: " + e.getMessage());
        }
    }

    // Método para obtener todas las alertas de la base de datos
    public static void obtenerAlertas() {
        String query = "SELECT * FROM alertas";

        try (Connection conexion = conectar();
             PreparedStatement stmt = conexion.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                int id = rs.getInt("id");
                int idTurbina = rs.getInt("id_turbina");
                String tipoAlerta = rs.getString("tipo_alerta");
                java.sql.Timestamp fechaAlerta = rs.getTimestamp("fecha_alerta");

                System.out.println("ID: " + id + ", ID Turbina: " + idTurbina + ", Tipo de Alerta: " + tipoAlerta + ", Fecha de Alerta: " + fechaAlerta);
            }

        } catch (SQLException e) {
            System.err.println("Error al obtener las alertas: " + e.getMessage());
        }
    }
    
    // Método para autenticar un usuario
    public static Usuario autenticarUsuario(String email, String contrasena) throws SQLException {
    String query = "SELECT * FROM usuarios WHERE email = ? AND contrasena = ?";
    try (Connection conexion = conectar();
         PreparedStatement stmt = conexion.prepareStatement(query)) {
        stmt.setString(1, email);
        stmt.setString(2, contrasena);
        ResultSet rs = stmt.executeQuery();
        if (rs.next()) {
            return new Usuario(
                rs.getInt("id"),
                rs.getString("nombre"),
                rs.getString("email"),
                rs.getString("rol")
            );
        } else {
            return null; // Usuario no encontrado
        }
    }
}
}