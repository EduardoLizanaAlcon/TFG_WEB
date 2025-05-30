<?php

class BarajaModelo{
        
        private $db;

        public function __construct(){
            $this->db = new Base;

        }
     

        public function ObtnerBaraja(){
            // Inicializar cURL
                $ch = curl_init();
    
            // Configurar opciones de cURL
                curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/new/shuffle/?cards=AS,2S,3S,4S,5S,6S,7S,JS,QS,KS,AC,2C,3C,4C,5C,6C,7C,JC,QC,KC,AD,2D,3D,4D,5D,6D,7D,JD,QD,KD,AH,2H,3H,4H,5H,6H,7H,JH,QH,KH');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            // Ejecutar la solicitud
                $response = curl_exec($ch);
    
            // Verificar si hubo un error
                if(curl_errno($ch)) {
                    echo 'Error en la solicitud: ' . curl_error($ch);
                } else {
                // Decodificar la respuesta JSON
                    $response = json_decode($response, true);
                }
    
            // Cerrar cURL
                curl_close($ch);
                
            return $response;
    
        }
    
        public function BarajarCartasConBaraja($idBaraja){
            // Inicializar cURL
                $ch = curl_init();
    
            // Configurar opciones de cURL
                curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/'.$idBaraja.'/shuffle/');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            // Ejecutar la solicitud
                $response = curl_exec($ch);
    
            // Verificar si hubo un error
                if(curl_errno($ch)) {
                    echo 'Error en la solicitud: ' . curl_error($ch);
                } else {
                // Decodificar la respuesta JSON
                    $response = json_decode($response, true);
                }
    
            // Cerrar cURL
                curl_close($ch);
            return $response;
    
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
                    $response = json_decode($response, true);
                }
    
            // Cerrar cURL
                curl_close($ch);
            return $response;
    
        }
        
        public function RobarCartas($idBaraja, $numeroCartas){
            // Inicializar cURL
                $ch = curl_init();
    
            // Configurar opciones de cURL
                curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/'.$idBaraja.'/draw/?count='.$numeroCartas);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            // Ejecutar la solicitud
                $response = curl_exec($ch);
    
            // Verificar si hubo un error
                if(curl_errno($ch)) {
                    echo 'Error en la solicitud: ' . curl_error($ch);
                } else {
                // Decodificar la respuesta JSON
                    $response = json_decode($response, true);
                }
    
            // Cerrar cURL
                curl_close($ch);
            return $response;
    
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
                    $response = json_decode($response, true);
                }
    
            // Cerrar cURL
                curl_close($ch);
            return $response;
    
        }
    
        public function AñadirCartaAGrupo($idBaraja, $idJugador, $idCarta, $idGrupo=""){
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
                    $response = json_decode($response, true);
                }
                if($idJugador =="triunfo"){
                    $this->db->query("SELECT `id` FROM `Partida` WHERE `ID_baraja` = '$idBaraja';");
                    $respuestaPartida = $this->db->registro();
                    if ($respuestaPartida->id != null) {
                        $partidaId = $respuestaPartida->id;
                        // 2. Buscar el set con mayor num_sets
                        $this->db->query("SELECT `partida_id`, `num_sets`, `puntuacion_equipo1`, `puntuacion_equipo2`
                                        FROM `sets`
                                        WHERE `partida_id` = :partidaId
                                        ORDER BY `num_sets` DESC
                                        LIMIT 1;");

                        $this->db->bind(':partidaId', $partidaId);
                        $respuestaSet = $this->db->registro();

                        if (!$respuestaSet) {
                            // 3. No hay sets → crear el primero con num_sets = 1
                            $this->db->query("INSERT INTO `sets` (`partida_id`, `num_sets`, `puntuacion_equipo1`, `puntuacion_equipo2`, `Triunfo`)
                                            VALUES (:partidaId, 1, 0, 0, :palo);");
                            $this->db->bind(':partidaId', $partidaId);
                            $this->db->bind(':palo', $idGrupo);
                            $this->db->execute();
                        } else {
                            // 4. Ya hay sets → comprobar si el máximo es menor que 3
                            $nuevoSet = (int)$respuestaSet->num_sets;
                            
                            if ($nuevoSet < 3) {
                                $nuevoSet += 1;
                                $this->db->query("INSERT INTO `sets` (`partida_id`, `num_sets`, `puntuacion_equipo1`, `puntuacion_equipo2`, `Triunfo`)
                                                VALUES (:partidaId, :numSets, 0, 0, :palo);");
                                $this->db->bind(':partidaId', $partidaId);
                                $this->db->bind(':numSets', $nuevoSet);
                                $this->db->bind(':palo', $idGrupo);
                                $this->db->execute();
                            }
                        }
                    }
                }
            // Cerrar cURL
                curl_close($ch);
            return $response;
    
        }
    
        public function ObservarTuscartas($idBaraja, $idJugador){
            // Inicializar cURL
                $ch = curl_init();
    
            // Configurar opciones de cURL
                curl_setopt($ch, CURLOPT_URL, 'https://deckofcardsapi.com/api/deck/'. $idBaraja .'/pile/'.$idJugador.'/list/');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
            // Ejecutar la solicitud
                $response = curl_exec($ch);
    
            // Verificar si hubo un error
                if(curl_errno($ch)) {
                    echo 'Error en la solicitud: ' . curl_error($ch);
                } else {
                // Decodificar la respuesta JSON
                    $response = json_decode($response, true);
                }
    
            // Cerrar cURL
                curl_close($ch);
            return $response;
    
        }

        public function TodosHanJugado($idBaraja) {
            
            $this->db->query("SELECT id, equipo1, equipo2 FROM Partida WHERE ID_baraja = :id");
            $this->db->bind(':id', $idBaraja);
            $jugadores = $this->db->registro();

                $response1 = $this->ObservarTuscartas($idBaraja, 'Carta_jugada_' . $jugadores->equipo1);
                $response2 = $this->ObservarTuscartas($idBaraja, 'Carta_jugada_' . $jugadores->equipo2);

                $this->db->query("SELECT `num_sets` FROM `sets` WHERE `partida_id` = :id_par ORDER BY `sets`.`num_sets` DESC limit 1;");
                $this->db->bind(':id_par', $jugadores->id);
                $registro = $this->db->registro();

                if (
                    !$response1['success'] || 
                    !$response2['success'] || 
                    empty($response1["piles"]['Carta_jugada_' . $jugadores->equipo1]['cards'])|| 
                    empty($response2["piles"]['Carta_jugada_' . $jugadores->equipo2]['cards'])
                ) {
                    return [
                        "success" => false,
                        "set" => $registro->num_sets??1,
                        "infoJugador1" => [
                            "id"=>$jugadores->equipo1,
                             "cartas" => $response1["piles"]['Carta_jugada_' . $jugadores->equipo1]?? null
                        ] ,
                        "infoJugador2" => [
                            "id"=>$jugadores->equipo2,
                             "cartas" => $response2["piles"]['Carta_jugada_' . $jugadores->equipo2]?? null
                        ] 
                    ];
                }
                    return [
                        "success" => true,
                        "set" => $registro->num_sets??1,
                        "infoJugador1" => [
                            "id"=>$jugadores->equipo1,
                             "cartas" => $response1["piles"]['Carta_jugada_' . $jugadores->equipo1]?? null
                        ] ,
                        "infoJugador2" => [
                            "id"=>$jugadores->equipo2,
                             "cartas" => $response2["piles"]['Carta_jugada_' . $jugadores->equipo2]?? null
                        ]
                    ];        
        }


        public function moverCartaAlCanto($idBaraja, $idJugador, $grupoCantoPrefix) {
            $equipo = in_array($idJugador, ['jugador1', 'jugador3']) ? 'equipo1' : 'equipo2';
            $pilaCartasGanadas = "cartasGanadas_$equipo";
        
            // Obtener cartas ganadas del equipo
            $cartasGanadas = $this->modeloBaraja->ObservarTuscartas($idBaraja, $pilaCartasGanadas);
        
            $cartas = [];
            if ($cartasGanadas['success'] && !empty($cartasGanadas['piles'][$pilaCartasGanadas]['cards'])) {
                $cartas = $cartasGanadas['piles'][$pilaCartasGanadas]['cards'];
            }
        
            if (!empty($cartas)) {
                // Elegir aleatoriamente una carta real del montón de ganadas
                $cartaAleatoria = $cartas[array_rand($cartas)];
                $codigoCarta = $cartaAleatoria['code'];
                $codigoCarta = $cartaAleatoria['suit'];
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
        
        
        public function GuardarPuntos($idBaraja, $idJugador, $puntos, $forzarArrastre) {
            $this->db->query("SELECT id, equipo1, equipo2 FROM Partida WHERE ID_baraja = :barajaId");
            $this->db->bind(':barajaId', $idBaraja);
            $partida = $this->db->registro();

            if (!$partida) return;

            $idPartida = $partida->id;
            $equipo1 = array_map('trim', explode(',', $partida->equipo1));
            $equipo2 = array_map('trim', explode(',', $partida->equipo2));

            $esEquipo1 = in_array($idJugador, $equipo1);
            $esEquipo2 = in_array($idJugador, $equipo2);
            if (!$esEquipo1 && !$esEquipo2) return;

            $this->db->query("SELECT num_sets, arrastre FROM sets WHERE partida_id = :partidaId ORDER BY num_sets DESC LIMIT 1");
            $this->db->bind(':partidaId', $idPartida);
            $set = $this->db->registro();
            if (!$set) return;

            $idSet = $set->num_sets;

            // Actualizar puntuación
            if ($esEquipo1) {
                $this->db->query("UPDATE sets SET puntuacion_equipo1 = puntuacion_equipo1 + :Puntos WHERE num_sets = :setid AND partida_id = :partidaId");
            } else {
                $this->db->query("UPDATE sets SET puntuacion_equipo2 = puntuacion_equipo2 + :Puntos WHERE num_sets = :setid AND partida_id = :partidaId");
            }
            $this->db->bind(':Puntos', $puntos);
            $this->db->bind(':setid', $idSet);
            $this->db->bind(':partidaId', $idPartida);
            $this->db->execute();

            // Si se fuerza el arrastre y aún no estaba activado, activarlo
            if ($forzarArrastre && $set->arrastre == 0) {
                $this->db->query("UPDATE sets SET arrastre = 1 WHERE num_sets = :setid AND partida_id = :partidaId");
                $this->db->bind(':setid', $idSet);
                $this->db->bind(':partidaId', $idPartida);
                $this->db->execute();
                $set->arrastre = 1; // lo forzamos en la variable también
            }

            if ($set->arrastre == 1) {
                $this->db->query("SELECT puntuacion_equipo1, puntuacion_equipo2 FROM sets WHERE num_sets = :setid AND partida_id = :partidaId");
                $this->db->bind(':setid', $idSet);
                $this->db->bind(':partidaId', $idPartida);
                $puntajes = $this->db->registro();

                $ganador = 'vueltas';

                if ($puntajes->puntuacion_equipo1 > 100) {
                    $ganador = 'equipo1';
                    
                } elseif ($puntajes->puntuacion_equipo2 > 100) {
                    $ganador = 'equipo2';
                }
                
                return [
                    'success' => true,
                    'puntos_equipo1' => $puntajes->puntuacion_equipo1,
                    'puntos_equipo2' => $puntajes->puntuacion_equipo2,
                    'ganador' => $ganador
                ];
            }
            return null;
        }


        public function GuardarJugada($idBaraja, $idJugador, $carta) {
            // Obtener la partida
            $this->db->query("SELECT id, equipo1, equipo2 FROM Partida WHERE ID_baraja = :barajaId");
            $this->db->bind(':barajaId', $idBaraja);
            $partida = $this->db->registro();

            if (!$partida) return;

            $idPartida = $partida->id;
            $equipo1 = array_map('trim', explode(',', $partida->equipo1));
            $equipo2 = array_map('trim', explode(',', $partida->equipo2));

            $esEquipo1 = in_array($idJugador, $equipo1);
            $esEquipo2 = in_array($idJugador, $equipo2);
            if (!$esEquipo1 && !$esEquipo2) return;

            // Obtener el último set
            $this->db->query("SELECT num_sets FROM sets WHERE partida_id = :partidaId ORDER BY num_sets DESC LIMIT 1");
            $this->db->bind(':partidaId', $idPartida);
            $set = $this->db->registro();
            if (!$set) return;
            $numSet = $set->num_sets;
            // Verificar si ya hay una jugada incompleta (una de las columnas equipo1/equipo2 es null)
            $this->db->query("SELECT id, equipo1, equipo2 FROM Jugadas WHERE ID_partida = :partidaId AND `set` = :setId");
            $this->db->bind(':partidaId', $idPartida);
            $this->db->bind(':setId', $numSet);
            $jugadas = $this->db->registros();

            foreach ($jugadas as $jugada) {
                if ($esEquipo1 && is_null($jugada->equipo1)) {

                    // Actualizar jugada existente para equipo1
                    $this->db->query("UPDATE Jugadas SET equipo1 = :carta WHERE id = :jugadaId");
                    $this->db->bind(':carta', $carta);
                    $this->db->bind(':jugadaId', $jugada->id);
                    $this->db->execute();
                    return ['success' => true, 'nueva' => false];
                }

                if ($esEquipo2 && is_null($jugada->equipo2)) {
                    // Actualizar jugada existente para equipo2
                    $this->db->query("UPDATE Jugadas SET equipo2 = :carta WHERE id = :jugadaId");
                    $this->db->bind(':carta', $carta);
                    $this->db->bind(':jugadaId', $jugada->id);
                    $this->db->execute();
                    return ['success' => true, 'nueva' => false];
                }
            }

            // Si no hay jugada incompleta, crear una nueva
            $this->db->query("INSERT INTO Jugadas (ID_partida, `set`, equipo1, equipo2) VALUES (:partidaId, :setId, :equipo1, :equipo2)");
            $this->db->bind(':partidaId', $idPartida);
            $this->db->bind(':setId', $numSet);
            if ($esEquipo1) {
                $this->db->bind(':equipo1', $carta);
                $this->db->bind(':equipo2', null);
            } else {
                $this->db->bind(':equipo1', null);
                $this->db->bind(':equipo2', $carta);
            }
            $this->db->execute();

            return ['success' => true, 'nueva' => true];
        }

}