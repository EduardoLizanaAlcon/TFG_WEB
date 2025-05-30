<?php

    class Inicio extends Controlador{

        private $SesionModelo;

        public function __construct(){

        }
        public function index(){   
            $this->vista('pagina_inicio');
        }


        public function edit(){
        }
    }