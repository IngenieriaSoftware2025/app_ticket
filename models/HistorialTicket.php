<?php

namespace Model;

class HistorialTicket extends ActiveRecord {
    
    public static $tabla = 'historial_incidentes_tickets';
    public static $columnasDB = [
        'hist_tic_id',
        'hist_tic_encargado',
        'hist_tic_solicitante',
        'hist_ticket',
        'hist_dependencia',
        'hist_tic_fecha_inicio',
        'hist_tic_fecha_finalizacion'
    ];

    public $hist_tic_id;
    public $hist_tic_encargado;
    public $hist_tic_solicitante;
    public $hist_ticket;
    public $hist_dependencia;
    public $hist_tic_fecha_inicio;
    public $hist_tic_fecha_finalizacion;

    public function __construct($argumentos = []) {
        $this->hist_tic_id = $argumentos['hist_tic_id'] ?? null;
        $this->hist_tic_encargado = $argumentos['hist_tic_encargado'] ?? 0;
        $this->hist_tic_solicitante = $argumentos['hist_tic_solicitante'] ?? 0;
        $this->hist_ticket = $argumentos['hist_ticket'] ?? '';
        $this->hist_dependencia = $argumentos['hist_dependencia'] ?? 0;
        $this->hist_tic_fecha_inicio = $argumentos['hist_tic_fecha_inicio'] ?? '';
        $this->hist_tic_fecha_finalizacion = $argumentos['hist_tic_fecha_finalizacion'] ?? '';
    }
}