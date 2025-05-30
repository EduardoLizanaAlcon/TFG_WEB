<?php
    //Acceso a URL

    // mapear la url ingresada en el navegador
    // 1- controlador
    // 2- metodo
    // 3- parametros
    // Ejmplo: /articulo/actualizar/4

    class Core{
        protected $controladorActual = 'Inicio';
        protected $metodoActual = 'index';
        protected $parametros = [];

        public function __construct(){
            $url = $this->getUrl();

            if(isset($url[0])){
                //Buscamos en controladores, si el controlador existe
                if(file_exists('../App/controladores/'.ucwords($url[0].'.php'))){
                    //Si existe, se configura como controlador por defecto
                    $this->controladorActual = ucwords($url[0]);
                    //eliminamos la primera posicion de $url,
                    unset($url[0]);

                }else{
                    echo "no existe el controlador";
                }
            }
            require_once '../App/controladores/'.$this->controladorActual.'.php';
            $this->controladorActual = new $this->controladorActual;

            //Obtener segunda parte del array
            if(isset($url[1])){
                if(method_exists($this->controladorActual,$url[1])){
                    $this->metodoActual = $url[1];
                    unset($url[1]);
                }else{
                    echo "El Metodo no existe";
                    exit;
                }
            }

            //Obtenemos los parametros (if simplificado)
            $this->parametros = $url ? array_values($url) : [];

            //Llamamos al metodo del controlador
            call_user_func_array([$this->controladorActual,$this->metodoActual],$this->parametros);

            //print_r($url);
        }

        //Transformamos la url en array
        private function getUrl(){
            if(isset($_GET['url'])){
                $url = $_GET['url'];
                $url = filter_var($url,FILTER_SANITIZE_URL); //Elimina caracteres raros.
                $url = rtrim($url,'/'); //limpiamos url por la derecha.
                $url = ltrim($url,'/'); //limpiamos url por la izquierda.
                $url_array = explode('/', $url);
                //print_r($url_array);
                return $url_array;
            }
        }
    }