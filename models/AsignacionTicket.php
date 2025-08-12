<?php

namespace Model;

class AsignacionTicket extends ActiveRecord {
    
    public static $tabla = 'tickets_asignados';
    public static $columnasDB = [
        'tic_id',
        'tic_numero_ticket',
        'tic_encargado',
        'estado_ticket',
        'tic_situacion'
    ];

    public $tic_id;
    public $tic_numero_ticket;
    public $tic_encargado;
    public $estado_ticket;
    public $tic_situacion;

    public function __construct($argumentos = []) {
        $this->tic_id = $argumentos['tic_id'] ?? null;
        $this->tic_numero_ticket = $argumentos['tic_numero_ticket'] ?? '';
        $this->tic_encargado = $argumentos['tic_encargado'] ?? 0;
        $this->estado_ticket = $argumentos['estado_ticket'] ?? 1; // Por defecto RECIBIDO
        $this->tic_situacion = $argumentos['tic_situacion'] ?? 1;
    }


}