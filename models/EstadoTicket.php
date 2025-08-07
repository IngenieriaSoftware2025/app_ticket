<?php

namespace Model;

class EstadoTicket extends ActiveRecord {
    
    public static $tabla = 'estado_ticket';
    public static $columnasDB = [
        'est_tic_id',
        'est_tic_desc'
    ];

    public $est_tic_id;
    public $est_tic_desc;

    public function __construct($argumentos = []) {
        $this->est_tic_id = $argumentos['est_tic_id'] ?? null;
        $this->est_tic_desc = $argumentos['est_tic_desc'] ?? '';
    }
}