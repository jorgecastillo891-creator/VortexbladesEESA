package vortexbladeless;

public interface Monitoreable {
    // Método para recolectar datos del dispositivo
    void recolectarDatos();

    // Método para detectar si hay alguna anomalía en el dispositivo
    boolean detectarAnomalia();

    // Método para generar un reporte del estado actual del dispositivo
    String generarReporte();
}
