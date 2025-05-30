<?php
class PartidaControlador extends Controlador {
  
    var $modeloBaraja;
    var $modeloPartida;
    public $datos;

    
    public function __construct(){
        $this->modeloBaraja = $this->modelo('BarajaModelo');
        $this->modeloPartida = $this->modelo('PartidaModelo');
    }
    
    
    public function CalcularGanador($idBaraja, $ganador, $jugadores) {  

        $puntosPorCarta = [
            'A' => 11, '3' => 10, 'K' => 4,
            'Q' => 2,  'J' => 3,  '7' => 0,
            '6' => 0,  '5' => 0,  '4' => 0, '2' => 0
        ];
    
        $resultado = [];
        $ganadorCot = [
               'sucess' => false,
               'ganador' => null,
            ];

        // Obtener cartas ganadas de cada jugador
        foreach ($jugadores as $jugador) {
            $cartasGanadas = $this->modeloBaraja->ObservarTuscartas($idBaraja, "cartasGanadas_$jugador");
          
            if($puntos > 50){
                $ganadorCot = [
                   'sucess' => true,
                   'ganador' => $jugador,
                ];
            };

            $resultado[$jugador] = [
                'puntos_cartas' => $puntos,
                'puntos_cantos' => 0,
                'total' => $puntos
            ];

        }

        return [
            'success' => true,
            'Puntuacion' => $resultado,
            'ganador' => $ganadorCot
        ];
    
    }

    public function calcularPuntos($idBaraja, $finPartida, $ganador, $cartasGanadas){

            $puntosPorCarta = [
                'A' => 11, '3' => 10, 'K' => 4,
                'Q' => 2,  'J' => 3,  '7' => 0,
                '6' => 0,  '5' => 0,  '4' => 0, '2' => 0
            ];

            $puntos = 0;
            if (!empty($cartasGanadas)) {
                foreach ($cartasGanadas as $carta) {
                    $valor = substr($carta, 0, -1);
                    $puntos += $puntosPorCarta[$valor] ?? 0;
                }
            };

            if($finPartida){
                $puntos += 10;
            }
            $this->modeloBaraja->AñadirCartaAGrupo($idBaraja, "yaJugadas", implode(',', $cartasGanadas));

            return $this->modeloBaraja->GuardarPuntos($idBaraja, $ganador, $puntos, $finPartida);
    }


    public function ObtenerJugadores(){
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        $this->datos = $this->modeloPartida->Emparejamiento($datos['id_usuario']);

        if ($this->datos['success'] ) {
            if($this->datos['PartidaCreada'] === false){
                $this->datos['InformacionCreada'] = $this->NuevaPartida($this->datos['creador'], $this->datos['seUne']);
                $this->datos['PartidaCreada'] =true;
            }  
        } else {
            $this->datos['mensaje'] = 'No se encontró un oponente disponible.';
        }
        $this->vistaApi($this->datos);
    }

    public function NuevaPartida($equipo1, $equipo2) {

        // Obtener nueva baraja
        $baraja = $this->modeloBaraja->ObtnerBaraja();

        $jugadores = [$equipo1, $equipo2];

    
        if ($baraja['success']) {
            $idBaraja = $baraja['deck_id'];

            $primerJugador = (rand(0, 1) == 0) ? $equipo1 : $equipo2;
            $devolver = $this->modeloPartida->CrearPartida($idBaraja, $equipo1, $equipo2, $primerJugador);

            $this->RepartoInicial($idBaraja, $jugadores);
            
            
            return $devolver;
                
        } else {
            $this->datos['error'] = 'No se pudo crear la baraja.';

            return false;
        }
    }
    
    public function RepartoInicial($idBaraja, $idJugadores) {

        // Decodifica si viene como JSON string
        $jugadores = is_string($idJugadores) ? json_decode($idJugadores, true) : $idJugadores;
    
        $this->modeloBaraja->BarajarCartasConBaraja($idBaraja);


        // Repartir 6 cartas por jugador
        foreach ($jugadores as $jugador) {
            $response = $this->modeloBaraja->RobarCartas($idBaraja, 6);
            $cartas = array_column($response['cards'], 'code');
    
            // Guardar cartas en pila del jugador
            $this->modeloBaraja->AñadirCartaAGrupo($idBaraja, $jugador, implode(',', $cartas));
        }
    
        // Robar carta de triunfo
        $triunfo = $this->modeloBaraja->RobarUnaCarta($idBaraja);
        $cartaTriunfo = $triunfo['cards'][0]['code'];
    
        // Guardarla en una pila especial llamada 'triunfo'
        $this->modeloBaraja->AñadirCartaAGrupo($idBaraja, 'triunfo', $cartaTriunfo, $cartaTriunfo = $triunfo['cards'][0]['suit']);          
    }
   
    public function MirarCartas() {
        // Leer el JSON desde el cuerpo de la solicitud POST
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true); // Convertir a array asociativo
    
        // Validar que se hayan recibido los datos esperados
        if (!isset($datos['idBaraja']) || !isset($datos['idJugador'])) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Faltan datos: idBaraja o idJugador'
            ];
            $this->vistaApi($this->datos);
            return;
        }
    
        // Obtener los valores del array
        $idBaraja = $datos['idBaraja'];
        $idJugador = $datos['idJugador'];
    
        // Obtener las cartas del jugador
        $response = $this->modeloBaraja->ObservarTuscartas($idBaraja, $idJugador);
    
        if ($response['success']) {
            $this->datos['cartas'] = $response['piles'][$idJugador]['cards'];
            $this->datos['success'] = true;
        } else {
            $this->datos = [
                'success' => false,
                'mensaje' => 'No se pudieron obtener las cartas.'
            ];
        }
    
        $this->vistaApi($this->datos);
    }

    public function RobarCarta($idBaraja, $idJugador) {
        // Robar una carta de la baraja
        $response = $this->modeloBaraja->RobarUnaCarta($idBaraja);
    
        if ($response['success']) {
            $carta = $response['cards'][0]['code'];
    
            // Añadir la carta a la pila del jugador
            $this->modeloBaraja->AñadirCartaAGrupo($idBaraja, $idJugador, $carta);
    
            // Guardar en datos para mostrar
            $this->datos['success'] = true;
            $this->datos['carta'] = $carta;
        } else {
            $this->datos['error'] = 'No se pudo robar la carta.';
        }
    
        $this->vistaApi($this->datos);
    }

    public function verCartaRival() {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        if (!isset($datos['idBaraja']) || !isset($datos['idJugador'])) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Faltan datos: idBaraja o idJugador'
            ];
            $this->vistaApi($this->datos);
            return;
        }
        
        $Jugadores = $this->modeloPartida->obtenerJugadores($datos['idBaraja']);
        if ($Jugadores->equipo1 == $datos['idJugador']) {
            $rival = $Jugadores->equipo2;
        } else {
            $rival = $Jugadores->equipo1;
        }


        $response = $this->modeloBaraja->ObservarTuscartas($datos['idBaraja'], "Carta_jugada_".$rival);

        $temp =  $this->modeloBaraja->ObservarTuscartas($datos['idBaraja'], "triunfo");

        if(empty($temp['piles']['triunfo']['cards'])){
            $arrastre = true;
        }else{
            $arrastre= false;
        }

        if ($response['success']) {
            $this->datos = [
                'success' => true,
                'cartasRival' => $response['piles']['Carta_jugada_'.$rival]['cards'],
                'arrastre' => $arrastre
            ];
        } else {
            $this->datos = [
                'success' => false,
                'mensaje' => 'No se pudieron obtener las cartas del rival'
            ];
        }

        $this->vistaApi($this->datos);
    }
    
    public function JugarCarta() {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        if (!isset($datos['idBaraja']) || !isset($datos['idJugador']) || !isset($datos['carta'])) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Faltan datos: idBaraja, idJugador o carta'
            ];
            $this->vistaApi($this->datos);
            return;
        }

        $enpieza = $this->modeloPartida->obtenerJugadorQueHaEmpezado($datos['idBaraja']);
        $primeroEnJugar = ($enpieza == $datos['idJugador']);

        $response = $this->modeloBaraja->ObservarTuscartas($datos['idBaraja'], $datos['idJugador']);
        $this->modeloPartida->actualizarSiguienteJugador($datos['idBaraja'], $datos['idJugador']);

        if ($response['success']) {
            $cartasJugador = $response['piles'][$datos['idJugador']]['cards'];
            $tieneCarta = false;

            foreach ($cartasJugador as $c) {
                if ($c['code'] == $datos['carta']) {
                    $tieneCarta = true;
                    break;
                }
            }

            if ($tieneCarta) {

                $this->modeloBaraja->GuardarJugada($datos['idBaraja'],  $datos['idJugador'], $datos['carta']);

                // Guardar la carta jugada
                $this->modeloBaraja->AñadirCartaAGrupo($datos['idBaraja'], "Carta_jugada_" . $datos['idJugador'], $datos['carta']);
                
                // Verificar si todos han jugado
                $todosHanJugado = $this->modeloBaraja->TodosHanJugado($datos['idBaraja']);
                $infoGanador = null;
                $jugadas = null;
                $ultimaJugada = false;

                // Si todos han jugado, procesar la mano
                if ($todosHanJugado['success']) {
                    $jugadas = [];
                    // Obtener información de la partida para determinar los equipos
                    $partida = $this->modeloPartida->obtenerJugadores($datos['idBaraja']);
                    
                    // Recorrer cada jugador que ha jugado
                    foreach ($todosHanJugado as $key => $info) {
                        if (strpos($key, 'infoJugador') === 0) {
                            $jugadorId = $info['id']; // Obtener el ID del jugador desde la info                         
                            $responseCartaJugada = $this->modeloBaraja->ObservarTuscartas($datos['idBaraja'], "Carta_jugada_".$jugadorId);

                            $responseCartasPorJugar = $this->modeloBaraja->ObservarTuscartas($datos['idBaraja'], $jugadorId);

                            if($responseCartasPorJugar['piles']['remaining'] = 0){
                                $ultimaJugada = true;
                            }

                            if ($responseCartaJugada['success'] && !empty($responseCartaJugada['piles']['Carta_jugada_'.$jugadorId]['cards'])) {
                                $cartaJugada = $responseCartaJugada['piles']['Carta_jugada_'.$jugadorId]['cards'][0];
                                
                                // Determinar a qué equipo pertenece el jugador
                                $equipo = ($jugadorId == $partida->equipo1 || $jugadorId == $partida->equipo2) ? 'equipo1' : 'equipo2';
                                
                                $jugadas[$jugadorId] = [
                                    'code' => $cartaJugada['code'],
                                    'suit' => $cartaJugada['suit'],
                                    'value' => $this->convertirValorCarta($cartaJugada['value']),
                                    'primeroenJugar' => ($jugadorId == $enpieza),
                                    'idCarta' => $cartaJugada['code'],
                                    'equipo' => $equipo
                                ];
                            }
                        }
                    }

                    // Procesar la mano
                    $infoGanador = $this->JugarMano($datos['idBaraja'], $jugadas, $ultimaJugada, $datos['arrastre']);
                }else {
                    $infoGanador = [
                        'success' => false,
                    ];
                }
                $this->datos = [
                    'success' => true,
                    'carta' => $datos['carta'],
                    'primeroenJugar' => $primeroEnJugar,
                    'infoRonda' => $todosHanJugado,
                    'infoGanador' => $infoGanador,
                ];

            } else {
                $this->datos = [
                    'success' => false,
                    'mensaje' => 'El jugador no tiene esa carta',
                    'carta' => '',
                    'primeroenJugar' => '',
                    'rondaCompleta' => false
                ];
            }
        } else {
            $this->datos = [
                'success' => false,
                'mensaje' => 'No se pudieron obtener las cartas del jugador',
                'carta' => '',
                'primeroenJugar' => '',
                'rondaCompleta' => false
            ];
        }

        $this->vistaApi($this->datos);
    }

    public function JugarMano($idBaraja, $jugadas, $ultimaJugada, $arrastre) {
        $infoConteo = null;
        // Obtener palo de triunfo
                $triunfoData = $this->modeloBaraja->ObservarTuscartas($idBaraja, 'triunfo');
                if(empty($triunfoData['piles']['triunfo']['cards'])){
                    $paloTriunfo = $this->modeloPartida->obtenerTriunfo($idBaraja);
                }else{
                    $paloTriunfo = $triunfoData['piles']['triunfo']['cards'][0]['suit'];
                }

        // Determinar orden de juego
            $ordenJugadores = [];
            $paloSalida = null;
            foreach ($jugadas as $jugador => $carta) {
                if ($carta['primeroenJugar']) {
                    $paloSalida = $carta['suit'];
                    array_unshift($ordenJugadores, $jugador);
                } else {
                    $ordenJugadores[] = $jugador;
                }
            }

            $jugadasOrdenadas = [];
            foreach ($ordenJugadores as $jugador) {
                $jugadasOrdenadas[$jugador] = $jugadas[$jugador];
            }

        // Determinar quién gana
            $ganador = null;
            $cartaGanadora = null;
            $cartasAGuardar = null; 
            $infoPartida = null;
            foreach ($jugadasOrdenadas as $jugador => $carta) {
                $cartasAGuardar[] = $carta['code'];
                if (!$cartaGanadora) {
                    $cartaGanadora = $carta;
                    $ganador = $jugador;
                    continue;
                }

                $esTriunfo = $carta['suit'] === $paloTriunfo;
                $actualEsTriunfo = $cartaGanadora['suit'] === $paloTriunfo;

                if ($esTriunfo && !$actualEsTriunfo) {
                    $cartaGanadora = $carta;
                    $ganador = $jugador;
                } elseif ($esTriunfo && $actualEsTriunfo && $carta['value'] > $cartaGanadora['value']) {
                    $cartaGanadora = $carta;
                    $ganador = $jugador;
                } elseif (!$esTriunfo && !$actualEsTriunfo) {
                if ($carta['suit'] === $paloSalida && $carta['value'] > $cartaGanadora['value']) {
                        $cartaGanadora = $carta;
                        $ganador = $jugador;
                    }
                }
            }

            $jugadores = array_keys($jugadas);
            $posGanador = array_search($ganador, $jugadores);

            // Reparto
            $ordenReparto = null;

            $ordenReparto = array_merge(
            array_slice($jugadores, $posGanador),
                array_slice($jugadores, 0, $posGanador)
            );        
            
            $this->modeloPartida->actualizarSiguienteJugadorMano($idBaraja, $ganador);

            if(!$arrastre){
                foreach ($ordenReparto as $jugador) {
                    $nueva = $this->modeloBaraja->RobarUnaCarta($idBaraja);
                    if ($nueva['success']) {
                        $code = $nueva['cards'][0]['code'];
                        $this->modeloBaraja->AñadirCartaAGrupo($idBaraja, $jugador, $code);
                    } else {
                        $response = $this->modeloBaraja->ObservarTuscartas($idBaraja, "triunfo");
                        $idTriunfo = $response['piles']['triunfo']['cards'][0]['code'];
                        $this->modeloBaraja->AñadirCartaAGrupo($idBaraja, $jugador, $idTriunfo);
                    }
                }
            }    
            
            $this->modeloPartida->GuardarGanador($idBaraja, $ganador);


            if (!$ultimaJugada) {
                $infoConteo = $this->calcularPuntos($idBaraja, false, $ganador, $cartasAGuardar);
                $estadoPartida = 'Continuar';

            } else {
                $infoConteo = $this->calcularPuntos($idBaraja, true, $ganador, $cartasAGuardar);

                    $n = $this->modeloPartida->obtenerNumSets($idBaraja);

                    if ($n < 3) {
                        
                        $this->RepartoInicial($idBaraja, $jugadores);
                        $estadoPartida = 'nuevo_set_creado';

                    }else {
                     // Si ya se jugaron los 3 sets o se detecta final definitivo
                            $infoPartida = $this->modeloPartida->esPartidaFinalizada($idBaraja);
                            $estadoPartida = 'partida_terminada';
                        
                    }               
            }

            $devolver = [
                'success' => true,
                'ganador' => $ganador,
                'cartasGanadas' => $cartasAGuardar,
                'cartasRepartidas' => $ordenReparto,
                'triunfo' => $paloTriunfo,
                'siguiente_jugador' => $ganador,
                'informacion_coto' => $infoConteo,
                'estado_partida' => $estadoPartida,
                'informacion_partida' => $infoPartida,
            ];


            return $devolver;
    }

        public function ObtenerGanador() {
            $json = file_get_contents('php://input');
            $datos = json_decode($json, true);

            if (!isset($datos['id_baraja'], $datos['id_carta'], $datos['set'])) {
                $this->datos = ['success' => false, 'mensaje' => 'Faltan parámetros (id_baraja, id_carta, set)'];
                $this->vistaApi($this->datos);
                return;
            }

            $idBaraja = $datos['id_baraja'];
            $idCarta = $datos['id_carta'];
            $numSet = $datos['set'];

            $resultados = $this->modeloPartida->obtenerGanadores($idBaraja, $idCarta, $numSet);

            if ($resultados) {
                $this->datos = [
                    'success' => true,
                    'ganador_jugada' => $resultados['ganador_jugada'],
                    'ganador_set' => $resultados['ganador_set'],
                    'puntos_equipo1' => $resultados['puntos_equipo1'],
                    'puntos_equipo2' => $resultados['puntos_equipo2'],
                    'ganador_partida' => $resultados['ganador_partida'],
                ];
            } else {
                $this->datos = [
                    'success' => false,
                    'mensaje' => 'No se encontró un ganador para esta jugada.'
                ];
            }

            $this->vistaApi($this->datos);
        }



    private function convertirValorCarta($valor) {
        $valores = [
            'ACE' => 14,
            'KING' => 13,
            'QUEEN' => 12,
            'JACK' => 11,
            '10' => 10,
            '9' => 9,
            '8' => 8,
            '7' => 7,
            '6' => 6,
            '5' => 5,
            '4' => 4,
            '3' => 3,
            '2' => 2
        ];
        return $valores[$valor] ?? (int)$valor;
    }
    
    public function ObtenerSiguienteJugador() {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        if (!isset($datos['id_partida'])) {
            $this->datos = ['success' => false, 'mensaje' => 'Falta el id_partida'];
            $this->vistaApi($this->datos);
            return;
        }

        $idPartida = $datos['id_partida'];
        $siguienteJugador = $this->modeloPartida->obtenerSiguienteJugador($idPartida);

        $this->datos = [
            'success' => true,
            'siguiente_jugador' => $siguienteJugador,
        ];



        $this->vistaApi($this->datos);
    }

    public function CantarLasVeinte() {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
    
        if (!isset($datos['idBaraja']) || !isset($datos['idJugador'])) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Faltan datos: idBaraja o idJugador'
            ];
            $this->vistaApi($this->datos);
            return;
        }
    
        $this->moverCartaAlCanto($datos['idBaraja'], $datos['idJugador'], 'canto20_');
    }
    
    public function CantarLasCuarenta() {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
    
        if (!isset($datos['idBaraja']) || !isset($datos['idJugador'])) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Faltan datos: idBaraja o idJugador'
            ];
            $this->vistaApi($this->datos);
            return;
        }
    
        $this->moverCartaAlCanto($datos['idBaraja'], $datos['idJugador'], 'canto40_');
    }
    
    private function moverCartaAlCanto($idBaraja, $idJugador, $grupoCantoPrefix) {
        $equipo = in_array($idJugador, ['jugador1', 'jugador3']) ? 'equipo1' : 'equipo2';
        $pilaCartasGanadas = "cartasGanadas_$equipo";
    
        // Obtener cartas ganadas del equipo
        $cartasGanadas = $this->modeloBaraja->ObservarTuscartas($idBaraja, $pilaCartasGanadas);
    
        $cartas = [];
        if ($cartasGanadas['success'] && !tempy($cartasGanadas['piles'][$pilaCartasGanadas]['cards'])) {
            $cartas = $cartasGanadas['piles'][$pilaCartasGanadas]['cards'];
        }
    
        if (!empty($cartas)) {
            // Elegir aleatoriamente una carta real del montón de ganadas
            $cartaAleatoria = $cartas[array_rand($cartas)];
            $codigoCarta = $cartaAleatoria['code'];
            $esFalsa = false;
        } else {
            // No hay cartas ganadas, meter un AS falso
            $palos = ['S', 'H', 'D', 'C']; // Espadas, Corazones, Diamantes, Tréboles
            $paloAleatorio = $palos[array_rand($palos)];
            $codigoCarta = 'AS' . $paloAleatorio; // Ej: ASH
            $esFalsa = true;
        }
    
        // Mover/agregar la carta al grupo de canto
        $this->modeloBaraja->AñadirCartaAGrupo($idBaraja, $grupoCantoPrefix . $idJugador, $codigoCarta);
    
        $this->datos = [
            'success' => true,
            'mensaje' => $esFalsa ? 'No había cartas ganadas, se insertó un AS ficticio.' : 'Carta real usada para el canto.',
            'cartaUsada' => $codigoCarta,
            'falsa' => $esFalsa,
            'grupoDestino' => $grupoCantoPrefix . $idJugador
        ];
        $this->vistaApi($this->datos);
    }

    private function valorCartaParaComparar($valor) {
        $valores = [
            'ACE' => 14, 'KING' => 13, 'QUEEN' => 12,
            'JACK' => 11, '10' => 10, '9' => 9,
            '8' => 8, '7' => 7, '6' => 6,
            '5' => 5, '4' => 4, '3' => 3, '2' => 2
        ];
        return $valores[$valor] ?? 0;
    }

    public function CambiarCartaTriunfo() {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        try {
            // Verificar si llegan todos los datos necesarios
            if (!isset($datos['id_baraja']) || !isset($datos['id_usuario']) || !isset($datos['id_Carta'])) {
                $this->datos = [
                    'success' => false,
                    'mensaje' => 'Faltan datos: id_baraja, id_usuario o id_Carta'
                ];
                $this->vistaApi($this->datos);
                return;
            }

            // Obtener la carta de triunfo actual
            $response = $this->modeloBaraja->ObservarTuscartas($datos['id_baraja'], "triunfo");
            if ($response['success'] && !empty($response['piles']["triunfo"]['cards'])) {
                $cartaTriunfo = $response['piles']["triunfo"]['cards'][0];
            } else {
                $this->datos = [
                    'success' => false,
                    'mensaje' => 'No se pudo obtener la carta de triunfo actual'
                ];
                $this->vistaApi($this->datos);
                return;
            }

            // Cambiar la carta de triunfo
            $this->modeloBaraja->AñadirCartaAGrupo($datos['id_baraja'], $datos['id_usuario'], $cartaTriunfo['code']);
            $this->modeloBaraja->AñadirCartaAGrupo($datos['id_baraja'], "triunfo", $datos['id_Carta']);

            $this->datos = [
                'success' => true,
                'mensaje' => 'Carta de triunfo cambiada exitosamente'
            ];
            $this->vistaApi($this->datos);
        } catch (Exception $e) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Error al cambiar carta de triunfo: ' . $e->getMessage()
            ];
            $this->vistaApi($this->datos);
        }
    }

}
