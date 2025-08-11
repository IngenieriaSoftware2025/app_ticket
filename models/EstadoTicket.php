<?php

namespace Model;

class EstadoTicket extends ActiveRecord {
    
    public static $tabla = 'estado_ticket';
    public static $columnasDB = [
        'est_tic_id',
        'est_tic_desc',
        'est_tic_situacion'
    ];

    public $est_tic_id;
    public $est_tic_desc;
    public $est_tic_situacion;

    public function __construct($argumentos = []) {
        $this->est_tic_id = $argumentos['est_tic_id'] ?? null;
        $this->est_tic_desc = $argumentos['est_tic_desc'] ?? '';
        $this->est_tic_situacion = $argumentos['est_tic_situacion'] ?? 1;
    }
}