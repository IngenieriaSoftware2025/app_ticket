<?php

namespace Model;

use Model\ActiveRecord;

class TicketAsignado extends ActiveRecord {
    
    public static $tabla = 'tickets_asignados';
    public static $idTabla = 'tic_id';
    public static $columnasDB = 
    [
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
    
    public function __construct($ticket = [])
    {
        $this->tic_id = $ticket['tic_id'] ?? null;
        $this->tic_numero_ticket = $ticket['tic_numero_ticket'] ?? '';
        $this->tic_encargado = $ticket['tic_encargado'] ?? 0;
        $this->estado_ticket = $ticket['estado_ticket'] ?? 1;
        $this->tic_situacion = $ticket['tic_situacion'] ?? 1;
    }

    public static function EliminarTicketAsignado($id){
        $sql = "DELETE FROM tickets_asignados WHERE tic_id = $id";
        return self::SQL($sql);
    }
}