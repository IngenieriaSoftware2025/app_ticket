<?php

namespace Model;

class FormularioTicket extends ActiveRecord {
    
    public static $tabla = 'formulario_ticket';
    public static $columnasDB = [
        'form_tick_num',
        'form_tic_usu', 
        'tic_dependencia',
        'tic_telefono',
        'tic_correo_electronico',
        'tic_app',
        'tic_comentario_falla',
        'tic_imagen',
        //'form_fecha_creacion',
        'form_estado'
    ];

    public $form_tick_num;
    public $form_tic_usu;
    public $tic_dependencia;
    public $tic_telefono;
    public $tic_correo_electronico;
    public $tic_app;
    public $tic_comentario_falla;
    public $tic_imagen;
    //public $form_fecha_creacion;
    public $form_estado;

    public function __construct($argumentos = []) {
        $this->form_tick_num = $argumentos['form_tick_num'] ?? '';
        $this->form_tic_usu = $argumentos['form_tic_usu'] ?? 0;
        $this->tic_dependencia = $argumentos['tic_dependencia'] ?? 0;
        $this->tic_telefono = $argumentos['tic_telefono'] ?? 0;
        $this->tic_correo_electronico = $argumentos['tic_correo_electronico'] ?? '';
        $this->tic_app = $argumentos['tic_app'] ?? 0;
        $this->tic_comentario_falla = $argumentos['tic_comentario_falla'] ?? '';
        $this->tic_imagen = $argumentos['tic_imagen'] ?? '';
        //$this->form_fecha_creacion = $argumentos['form_fecha_creacion'] ?? '';
        $this->form_estado = $argumentos['form_estado'] ?? 1;
    }
}