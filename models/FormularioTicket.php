<?php

namespace Model;

class FormulariTicket extends ActiveRecord {
    public static $tabla = 'formulario_ticket';
    public static $columnasDB = [
        'form_tick_num',
        'form_tic_usu',
        'tic_dependencia',
        'tic_comentario_falla',
        'tic_correo_electronico',
        'form_ticket_num',
    ];
}