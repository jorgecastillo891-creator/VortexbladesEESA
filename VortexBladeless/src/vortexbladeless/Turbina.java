package vortexbladeless;

import java.util.Date;
import java.util.Random;

public class Turbina implements Monitoreable {
    private int id;
    private String ubicacion;
    private float vibracion;
    private float temperatura;
    private String estado;
    private Date fechaInstalacion;

    // Constructor
    public Turbina(int id, String ubicacion, Date fechaInstalacion) {
        this.id = id;
        this.ubicacion = ubicacion;
        this.fechaInstalacion = fechaInstalacion;
        this.estado = "En funcionamiento";
        this.vibracion = 0.0f;
        this.temperatura = 0.0f;
    }

    // Métodos de la interfaz Monitoreable
    @Override
    public void recolectarDatos() {
        Random random = new Random();
        this.vibracion = random.nextFloat() * 10; // Simula vibración entre 0 y 10
        this.temperatura = random.nextFloat() * 100; // Simula temperatura entre 0 y 100
        System.out.println("Datos recolectados para la turbina " + id + ": Vibración = " + vibracion + ", Temperatura = " + temperatura);
    }

    @Override
    public boolean detectarAnomalia() {
        // Ejemplo simple de detección de anomalía
        if (vibracion > 7.0f || temperatura > 80.0f) {
            this.estado = "Requiere mantenimiento";
            return true;
        }
        this.estado = "En funcionamiento";
        return false;
    }

    @Override
    public String generarReporte() {
        return "Turbina ID: " + id + "\nUbicación: " + ubicacion + "\nEstado: " + estado +
                "\nVibración: " + vibracion + "\nTemperatura: " + temperatura + "\nFecha de Instalación: " + fechaInstalacion;
    }

    // Getters y Setters
    public int getId() {
        return id;
    }

    public String getUbicacion() {
        return ubicacion;
    }

    public float getVibracion() {
        return vibracion;
    }

    public float getTemperatura() {
        return temperatura;
    }

    public String getEstado() {
        return estado;
    }

    public Date getFechaInstalacion() {
        return fechaInstalacion;
    }
}
