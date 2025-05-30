<?php
class BarajaControlador extends Controlador {
    var $modeloBaraja;
        
    public $datos;

    
    public function __construct(){
        $this->modeloBaraja = $this->modelo('BarajaModelo');

    }
    public function index(){
    }

    public function ObtnerBaraja(){

        $this->datos = $this->modeloBaraja->ObtnerBaraja();
        
        $this->vistaApi($this->datos);

    }


    public function RobarUnaCarta($idBaraja){
        // Inicializar cURL
            $ch = curl_init();

        // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/'.$idBaraja.'/draw/?count=1');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud
            $response = curl_exec($ch);

        // Verificar si hubo un error
            if(curl_errno($ch)) {
                echo 'Error en la solicitud: ' . curl_error($ch);
            } else {
            // Decodificar la respuesta JSON
                $this->datos = json_decode($response, true);
            }

        // Cerrar cURL
            curl_close($ch);
        $this->vistaApi($this->datos);

    }
    
    public function BarajarCartas($idBaraja){
        // Inicializar cURL
            $ch = curl_init();

        // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/'.$idBaraja.'/shuffle/?remaining=true');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud
            $response = curl_exec($ch);

        // Verificar si hubo un error
            if(curl_errno($ch)) {
                echo 'Error en la solicitud: ' . curl_error($ch);
            } else {
            // Decodificar la respuesta JSON
                $this->datos = json_decode($response, true);
            }

        // Cerrar cURL
            curl_close($ch);
        $this->vistaApi($this->datos);

    }

    public function AñadirCartaAGrupo($idBaraja, $idJugador, $idCarta){
        // Inicializar cURL
            $ch = curl_init();

        // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/'.$idBaraja.'/pile/'.$idJugador.'/add/?cards='.$idCarta);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud
            $response = curl_exec($ch);

        // Verificar si hubo un error
            if(curl_errno($ch)) {
                echo 'Error en la solicitud: ' . curl_error($ch);
            } else {
            // Decodificar la respuesta JSON
                $this->datos = json_decode($response, true);
            }

        // Cerrar cURL
            curl_close($ch);
        $this->vistaApi($this->datos);

    }

    public function ObservarTuscartas($idBaraja, $idJugador, $idCarta){
        // Inicializar cURL
            $ch = curl_init();

        // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/<<deck_id>>/pile/'.$iud.'/list/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud
            $response = curl_exec($ch);

        // Verificar si hubo un error
            if(curl_errno($ch)) {
                echo 'Error en la solicitud: ' . curl_error($ch);
            } else {
            // Decodificar la respuesta JSON
                $this->datos = json_decode($response, true);
            }

        // Cerrar cURL
            curl_close($ch);
        $this->vistaApi($this->datos);

    }

    public function GanadorRonda($idBaraja, $idPila, $idCarta){
        // Inicializar cURL
            $ch = curl_init();

        // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/'.$idBaraja.'/pile/'.$idPila.'/add/?cards='.$idCarta);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud
            $response = curl_exec($ch);

        // Verificar si hubo un error
            if(curl_errno($ch)) {
                echo 'Error en la solicitud: ' . curl_error($ch);
            } else {
            // Decodificar la respuesta JSON
                $this->datos = json_decode($response, true);
            }

        // Cerrar cURL
            curl_close($ch);
        $this->vistaApi($this->datos);

    }
}
