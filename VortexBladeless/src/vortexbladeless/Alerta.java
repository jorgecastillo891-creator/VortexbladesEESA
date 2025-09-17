package vortexbladeless;

import java.util.Date;

public class Alerta implements Notificable {
    private int id; // Este id será generado automáticamente por la base de datos
    private int idTurbina; // ID de la turbina que generó la alerta
    private int idTecnico; // ID del técnico que generó la alerta
    private String tipoAlerta; // Tipo de alerta (descripción)
    private Date fechaAlerta; // Fecha de creación de la alerta

    // Constructor que incluye el ID del técnico
    public Alerta(int idTurbina, String tipoAlerta, int idTecnico) {
        this.idTurbina = idTurbina;
        this.tipoAlerta = tipoAlerta;
        this.idTecnico = idTecnico;
        this.fechaAlerta = new Date(); // Fecha actual de creación de la alerta
    }

    // Métodos de la interfaz Notificable
    @Override
    public void enviarNotificacion() {
        System.out.println("Alerta generada por el técnico " + idTecnico + 
                " para la turbina " + idTurbina + ": " + tipoAlerta +
                " - Fecha: " + fechaAlerta);
    }

    @Override
    public String mostrarDetalle() {
        return "Turbina ID: " + idTurbina + "\nTécnico ID: " + idTecnico + 
               "\nTipo de Alerta: " + tipoAlerta +
               "\nFecha de Alerta: " + fechaAlerta;
    }

    // Getters y Setters
    public int getId() {
        return id; // El ID se puede asignar más tarde si es necesario
    }

    public void setId(int id) {
        this.id = id; // En caso de necesitar asignarlo luego de obtener la alerta desde la base de datos
    }

    public int getIdTurbina() {
        return idTurbina;
    }

    public int getIdTecnico() {
        return idTecnico;
    }

    public void setIdTecnico(int idTecnico) {
        this.idTecnico = idTecnico;
    }

    public String getTipoAlerta() {
        return tipoAlerta;
    }

    public Date getFechaAlerta() {
        return fechaAlerta;
    }
}
