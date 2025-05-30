<?php

class PartidaModelo{
        
        private $db;

        public function __construct(){
            $this->db = new Base;

        }
     

        public function Emparejamiento($idUsuario) {
            // Verificar si ya está emparejado
            $this->db->query("SELECT Id_emparejado FROM ColaBusqueda WHERE usuario_id = :id AND estado = 'emparejado';");
            $this->db->bind(':id', $idUsuario);
            $emparejamientoExistente = $this->db->registro();

            if ($emparejamientoExistente) {
                $reintentos = 3;
                $espera = 3; // segundos
                $registro = null;

                while ($reintentos > 0) {
                    $this->db->query("SELECT `ColaBusqueda`.`ID_partida`, Partida.equipo1, Partida.ID_baraja, Partida.equipo2 
                                    FROM `ColaBusqueda` 
                                    INNER JOIN Partida ON Partida.id = ColaBusqueda.ID_partida  
                                    WHERE ColaBusqueda.usuario_id = :id 
                                    LIMIT 1;");
                    $this->db->bind(':id', $idUsuario);
                    $registro = $this->db->registro();

                    // Verifica si algún campo es null
                    if ($registro && $registro->ID_partida !== null && $registro->equipo1 !== null && 
                        $registro->ID_baraja !== null && $registro->equipo2 !== null) {
                        break; // todos los campos son válidos, salimos del bucle
                    }

                    sleep($espera); // espera 3 segundos antes de reintentar
                    $reintentos--;
                }

                // Si después de los reintentos aún hay campos nulos
                if (!$registro || $registro->ID_partida === null || $registro->equipo1 === null || 
                    $registro->ID_baraja === null || $registro->equipo2 === null) {
                    return [
                        'success' => false,
                        'error' => 'No se pudo obtener información válida de la partida.'
                    ];
                }

                return [
                    'success' => true,
                    'creador' => $emparejamientoExistente->Id_emparejado,
                    'seUne' => $idUsuario,
                    'PartidaCreada' => true,
                    'InformacionCreada' => [
                        'success' => true,
                        'equipo1' => (string) $registro->equipo1,
                        'equipo2' => (string) $registro->equipo2,
                        'id_baraja' => $registro->ID_baraja,
                        'id_partida' => $registro->ID_partida,
                    ]
                ];
            }


            // Obtener número de partidas ganadas
            $this->db->query("SELECT partidas_ganadas FROM Usuarios WHERE id = :id;");
            $this->db->bind(':id', $idUsuario);
            $partidas_ganadas = $this->db->registro();

            // Insertar o actualizar en la cola
            $this->db->query("INSERT INTO ColaBusqueda (usuario_id, fecha_busqueda, partidas_ganadas, estado)
                            VALUES (:id, NOW(), :partidas, 'buscando')
                            ON DUPLICATE KEY UPDATE fecha_busqueda = NOW(), estado = 'buscando', partidas_ganadas = :partidas;");
            $this->db->bind(':id', $idUsuario);
            $this->db->bind(':partidas', $partidas_ganadas->partidas_ganadas);
            $this->db->execute();

            // Buscar oponente
            $this->db->query("SELECT usuario_id FROM ColaBusqueda WHERE estado = 'buscando' AND usuario_id != :id ORDER BY fecha_busqueda DESC LIMIT 1;");
            $this->db->bind(':id', $idUsuario);
            $oponente = $this->db->registro();

            if ($oponente) {
                // Emparejar a ambos
                $this->db->query("UPDATE ColaBusqueda
                                SET estado = 'emparejado',
                                    Id_emparejado = CASE 
                                        WHEN usuario_id = :id THEN :oponente
                                        WHEN usuario_id = :oponente THEN :id
                                    END
                                WHERE usuario_id IN (:id, :oponente);");
                $this->db->bind(':id', $idUsuario);
                $this->db->bind(':oponente', $oponente->usuario_id);
                $this->db->execute();

                return [
                    'success' => true,
                    'creador' => $oponente->usuario_id,
                    'seUne' => $idUsuario,
                    'PartidaCreada' => false
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Esperando oponente...'
                ];
            }
        }

        public function obtenerTriunfo($idBaraja){
            $this->db->query("SELECT `id` FROM `Partida` WHERE `ID_baraja` = :idBaraja LIMIT 1;");
            $this->db->bind(':idBaraja', $idBaraja);
            $respuestaPartida = $this->db->registro();

            if (!$respuestaPartida) return false;

            $partidaId = $respuestaPartida->id;

            $this->db->query("SELECT `Triunfo` FROM `sets` WHERE `partida_id` = :partidaId ORDER BY `num_sets` DESC LIMIT 1;");
            $this->db->bind(':partidaId', $partidaId);
            $resultado = $this->db->registro();
            return $resultado->Triunfo;
        }

        public function CrearPartida($idBaraja, $equipo1, $equipo2, $primerJugador) {
            // Crear la partida
            $this->db->query("INSERT INTO Partida (id_baraja, equipo1, equipo2, estado, empieza_jugador, siguiente_jugador) VALUES (:idBaraja, :jugador1, :jugador2, 'en curso', :primerJugador, :primerJugador)");            
            $this->db->bind(':idBaraja', $idBaraja);
            $this->db->bind(':jugador1', $equipo1);
            $this->db->bind(':jugador2', $equipo2);
            $this->db->bind(':primerJugador', $primerJugador);

            if ($this->db->execute()) {
                $idPartida = $this->db->lastInsertId(); // Obtener ID de la partida creada

                // Actualizar ColaBusqueda para ambos jugadores con el ID de la partida
                $this->db->query("UPDATE ColaBusqueda SET ID_partida = :idPartida WHERE usuario_id IN (:jugador1, :jugador2);");
                $this->db->bind(':idPartida', $idPartida);
                $this->db->bind(':jugador1', $equipo1);
                $this->db->bind(':jugador2', $equipo2);
                $this->db->execute();

                return [
                    'success' => true,
                    'equipo1' => (string) $equipo1,
                    'equipo2' => (string) $equipo2,
                    'id_baraja' => $idBaraja,
                    'id_partida' => (int) $idPartida,
                ];
            } else {
                return [
                    'success' => false,
                ];
            }
        }
     
        public function verificarGanadorPartida($idBaraja) {
            $this->db->query("SELECT `id` FROM `Partida` WHERE `ID_baraja` = :idBaraja LIMIT 1;");
            $this->db->bind(':idBaraja', $idBaraja);
            $partida = $this->db->registro();
            if (!$partida) return null;

            $partidaId = $partida->id;

            $this->db->query("SELECT 
                SUM(CASE WHEN puntuacion_equipo1 > puntuacion_equipo2 THEN 1 ELSE 0 END) AS sets_ganados_equipo1,
                SUM(CASE WHEN puntuacion_equipo2 > puntuacion_equipo1 THEN 1 ELSE 0 END) AS sets_ganados_equipo2
                FROM sets WHERE partida_id = :partidaId;");
            $this->db->bind(':partidaId', $partidaId);
            $resultado = $this->db->registro();

            if (!$resultado) return null;

            if ($resultado->sets_ganados_equipo1 >= 2) return 'equipo1';
            if ($resultado->sets_ganados_equipo2 >= 2) return 'equipo2';

            return null; // aún no hay ganador
        }

        public function finalizarPartida($idBaraja, $ganador) {
            $this->db->query("SELECT `id` FROM `Partida` WHERE `ID_baraja` = :idBaraja LIMIT 1;");
            $this->db->bind(':idBaraja', $idBaraja);
            $partida = $this->db->registro();
            if (!$partida) return;

            $this->db->query("UPDATE Partida SET estado = 'finalizada', equipo_ganador = :ganador WHERE id = :partidaId;");
            $this->db->bind(':ganador', $ganador);
            $this->db->bind(':partidaId', $partida->id);
            $this->db->execute();
        }

        public function actualizarSiguienteJugador($idPartida, $idJugador) {
            // Obtener el otro jugador en la partida
            $this->db->query("SELECT equipo1, equipo2 FROM Partida WHERE ID_baraja = :id");
            $this->db->bind(':id', $idPartida);
            $partida = $this->db->registro();

            if (!$partida) {
                return false; // No se encontró la partida
            }

            // Determinar el siguiente jugador
                $siguienteJugador = ($partida->equipo1 == $idJugador) ? $partida->equipo2 : $partida->equipo1;

            // Actualizar siguiente_jugador
            $this->db->query("UPDATE Partida SET siguiente_jugador = :jugador WHERE ID_baraja = :id");
            $this->db->bind(':jugador', $siguienteJugador);
            $this->db->bind(':id', $idPartida);

            return $this->db->execute();
        }

        
        public function actualizarSiguienteJugadorMano($idPartida, $idJugador) {
            // Obtener el otro jugador en la partida
            $this->db->query("SELECT equipo1, equipo2 FROM Partida WHERE ID_baraja = :id");
            $this->db->bind(':id', $idPartida);
            $partida = $this->db->registro();

            if (!$partida) {
                return false; // No se encontró la partida
            }

            // Actualizar siguiente_jugador
            $this->db->query("UPDATE Partida SET siguiente_jugador = :jugador, empieza_jugador = :jugador WHERE ID_baraja = :id");
            $this->db->bind(':jugador', $idJugador);
            $this->db->bind(':id', $idPartida);

            return $this->db->execute();
        }
            
        public function obtenerSiguienteJugador($idPartida) {
            $this->db->query("SELECT siguiente_jugador FROM Partida WHERE ID_baraja = :id");
            $this->db->bind(':id', $idPartida);
            return $this->db->registro()->siguiente_jugador;
        } 
          
        public function obtenerJugadorQueHaEmpezado($idPartida) {
            $this->db->query("SELECT empieza_jugador FROM Partida WHERE ID_baraja = :id");
            $this->db->bind(':id', $idPartida);
            return $this->db->registro()->empieza_jugador;
        }   
        
        public function obtenerJugadores($idPartida) {
            $this->db->query("SELECT equipo1, equipo2 FROM Partida WHERE ID_baraja = :id");
            $this->db->bind(':id', $idPartida);
            return $this->db->registro();
        }

        public function GuardarGanador($idBaraja, $ganador){
            // Guardar el ganador en la tabla Jugadas
            $this->db->query("SELECT id FROM Partida WHERE ID_baraja = :idBaraja");
            $this->db->bind(':idBaraja', $idBaraja);
            $partida = $this->db->registro();

            if ($partida) {
                $idPartida = $partida->id;

                // Obtener el set actual
                $this->db->query("SELECT num_sets FROM sets WHERE partida_id = :partidaId ORDER BY num_sets DESC LIMIT 1");
                $this->db->bind(':partidaId', $idPartida);
                $set = $this->db->registro();

                if ($set) {
                    $numSet = $set->num_sets;

                    // Obtener la última jugada del set actual
                    $this->db->query("SELECT id FROM Jugadas WHERE ID_partida = :partidaId AND `set` = :numSet ORDER BY id DESC LIMIT 1");
                    $this->db->bind(':partidaId', $idPartida);
                    $this->db->bind(':numSet', $numSet);
                    $jugada = $this->db->registro();

                    if ($jugada) {
                        // Actualizar la jugada con el ganador
                        $this->db->query("UPDATE Jugadas SET ganador = :ganador WHERE id = :jugadaId");
                        $this->db->bind(':ganador', $ganador);
                        $this->db->bind(':jugadaId', $jugada->id);
                        $this->db->execute();
                    }
                }
            }
        }

        public function obtenerGanadores($idBaraja, $carta, $numSet) {
            // 1. Obtener ID de partida
            $this->db->query("SELECT id FROM Partida WHERE ID_baraja = :idBaraja");
            $this->db->bind(':idBaraja', $idBaraja);
            $partida = $this->db->registro();

            if (!$partida) return null;

            $idPartida = $partida->id;

            // 2. Buscar jugada en ese set con esa carta
            $this->db->query("SELECT * FROM Jugadas 
                WHERE ID_partida = :partidaId 
                AND `set` = :setId 
                AND (equipo1 = :carta OR equipo2 = :carta)
                ORDER BY id DESC 
                LIMIT 1
            ");
            $this->db->bind(':partidaId', $idPartida);
            $this->db->bind(':setId', $numSet);
            $this->db->bind(':carta', $carta);
            $jugada = $this->db->registro();

            if (!$jugada || !$jugada->ganador) {
                return null; // No hay jugada ganadora
            }

            $ganadorJugada = $jugada->ganador;

            // 3. Verificar si el set ya tiene ganador y puntos
            $this->db->query("SELECT puntuacion_equipo1, puntuacion_equipo2, ganador 
                FROM sets 
                WHERE partida_id = :partidaId AND num_sets = :setId
            ");
            $this->db->bind(':partidaId', $idPartida);
            $this->db->bind(':setId', $numSet);
            $set = $this->db->registro();

            $ganadorSet = $set && $set->ganador ? $set->ganador : null;
            $puntosEquipo1 = $set ? $set->puntuacion_equipo1 : 0;
            $puntosEquipo2 = $set ? $set->puntuacion_equipo2 : 0;

            // 4. Si es el tercer set, determinar el ganador de la partida
            $ganadorPartida = null;
            if ($numSet == 3) {
                $this->db->query("SELECT ganador, COUNT(*) as ganados
                    FROM sets 
                    WHERE partida_id = :partidaId AND ganador IS NOT NULL
                    GROUP BY ganador
                    ORDER BY ganados DESC
                    LIMIT 1
                ");
                $this->db->bind(':partidaId', $idPartida);
                $mayorGanador = $this->db->registro();

                if ($mayorGanador) {
                    $ganadorPartida = $mayorGanador->ganador;
                }
            }

            return [
                'ganador_jugada' => $ganadorJugada,
                'ganador_set' => $ganadorSet,
                'puntos_equipo1' => $puntosEquipo1,
                'puntos_equipo2' => $puntosEquipo2,
                'ganador_partida' => $ganadorPartida
            ];
        }



        public function obtenerNumSets($idBaraja) {
            $this->db->query("SELECT COUNT(*) as num_sets FROM sets WHERE partida_id = (SELECT id FROM Partida WHERE ID_baraja = :idBaraja)");
            $this->db->bind(':idBaraja', $idBaraja);
            $resultado = $this->db->registro();
            return $resultado ? (int)$resultado->num_sets : 0;
        }
     
        public function esPartidaFinalizada($idBaraja) {
            // Paso 1: obtener el ganador
            $this->db->query(" SELECT p.id AS partida_id,
                    CASE 
                        WHEN s.sets_ganados_equipo1 > s.sets_ganados_equipo2 THEN p.equipo1
                        WHEN s.sets_ganados_equipo2 > s.sets_ganados_equipo1 THEN p.equipo2
                        ELSE 'Empate'
                    END AS ganador
                FROM Partida p
                JOIN (
                    SELECT partida_id, 
                        SUM(CASE WHEN puntuacion_equipo1 > puntuacion_equipo2 THEN 1 ELSE 0 END) AS sets_ganados_equipo1,
                        SUM(CASE WHEN puntuacion_equipo2 > puntuacion_equipo1 THEN 1 ELSE 0 END) AS sets_ganados_equipo2
                    FROM sets
                    GROUP BY partida_id
                ) s ON p.id = s.partida_id
                WHERE p.ID_baraja = :idBaraja
                LIMIT 1;
            ");
            $this->db->bind(':idBaraja', $idBaraja);
            $resultado = $this->db->registro();

            if ($resultado) {
                $partidaId = $resultado->partida_id;
                $ganador = $resultado->ganador;

                // Paso 2: actualizar el campo ganador en la tabla Partida
                $this->db->query(" UPDATE Partida
                    SET ganador = :ganador
                    WHERE id = :partidaId
                ");
                $this->db->bind(':ganador', $ganador);
                $this->db->bind(':partidaId', $partidaId);
                $this->db->execute();
            }

            $this->db->query(" DELETE FROM ColaBusqueda
                WHERE ID_partida = :idPartida
            ");
            $this->db->bind(':idPartida', $partidaId);
            $this->db->execute();

            return [
                'success' => true,
                'partida_id' => $partidaId,
                'ganador' => $ganador,
                'finalizada' => true
            ];
        }

    }