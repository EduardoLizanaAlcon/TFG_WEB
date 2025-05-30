<?php

    class SesionModelo{
        private $db;

        public function __construct(){
            $this->db = new Base;
        }

        public function comprobacion($u, $c){
            

            $this->db->query("SELECT * FROM `Usuarios` WHERE `usuario`=:u AND `contrasena`= :c;");
            $this->db->bind(':u', $u);
            $this->db->bind(':c', $c);

            if( $this->db->rowCount()>0){
                
                return $this->db->registro();

            }else{
                return null; 
            }

            exit;

        }

        public function registrarUsuario($usuario, $contrasena, $nombre, $apellido, $num_tel) {
            // Insertar usuario
                $this->db->query("INSERT INTO `Usuarios` (`usuario`, `contrasena`, `nombre`, `apellido`, `num_tel`) 
                                     VALUES (:usuario, :contrasena, :nombre, :apellido, :num_tel)");

                $this->db->bind(':usuario', $usuario);
                $this->db->bind(':contrasena', $contrasena);
                $this->db->bind(':nombre', $nombre);
                $this->db->bind(':apellido', $apellido);
                $this->db->bind(':num_tel', $num_tel);

            if ($this->db->execute()) {
                // Recuperar el usuario insertado
                $this->db->query("SELECT * FROM `Usuarios` WHERE `usuario` = :usuario");
                $this->db->bind(':usuario', $usuario);
                $this->db->execute();
                $usuarioDatos = $this->db->registro();

                return [
                'success' => true,
                'mensaje' => 'Usuario registrado correctamente',
                'usuario' => $usuarioDatos
                ];
            } 
            else {
                return [
                    'success' => false,
                    'mensaje' => 'Error al registrar usuario',
                    'usuario' => []
                ];
            }
        }
        

        public function editarPerfil($id_usuario, $usuario, $nombre, $apellido, $num_tel) {
            // Actualizar datos del usuario
            $this->db->query("UPDATE `Usuarios` 
                            SET `usuario` = :usuario, 
                                `nombre` = :nombre, 
                                `apellido` = :apellido, 
                                `num_tel` = :num_tel 
                            WHERE `id` = :id_usuario");

            $this->db->bind(':usuario', $usuario);
            $this->db->bind(':nombre', $nombre);
            $this->db->bind(':apellido', $apellido);
            $this->db->bind(':num_tel', $num_tel);
            $this->db->bind(':id_usuario', $id_usuario);

            if ($this->db->execute()) {
                // Recuperar los datos actualizados
                $this->db->query("SELECT * FROM `Usuarios` WHERE `id` = :id_usuario");
                $this->db->bind(':id_usuario', $id_usuario);
                $this->db->execute();
                $usuarioDatos = $this->db->registro();

                return [
                    'success' => true,
                    'mensaje' => 'Perfil actualizado correctamente',
                    'usuario' => $usuarioDatos
                ];
            } 
            else {
                return [
                    'success' => false,
                    'mensaje' => 'Error al actualizar perfil',
                    'usuario' => []
                ];
            }
        }


        public function comprobarHistorico($id_usuario){
            $this->db->query("SELECT 
                                GROUP_CONCAT(DISTINCT CONCAT(U1.nombre, ' ', U1.apellido) SEPARATOR ', ') AS equipo1_nombres,
                                GROUP_CONCAT(DISTINCT CONCAT(U2.nombre, ' ', U2.apellido) SEPARATOR ', ') AS equipo2_nombres,
                                
                                CASE
                                    WHEN FIND_IN_SET(:id, P.equipo1) > 0 AND P.ganador = 0 THEN 'Ganada'
                                    WHEN FIND_IN_SET(:id, P.equipo2) > 0 AND P.ganador = 1 THEN 'Ganada'
                                    ELSE 'Perdida'
                                END AS resultado,

                                -- Concatenamos los sets
                                GROUP_CONCAT(CONCAT('Set ', S.num_sets, ': ', S.puntuacion_equipo1, '-', S.puntuacion_equipo2) SEPARATOR ', ') AS detalle_sets
                            FROM 
                                Partida P
                            JOIN 
                                sets S ON S.partida_id = P.id
                            LEFT JOIN 
                                Usuarios U1 ON FIND_IN_SET(U1.id, P.equipo1)
                            LEFT JOIN 
                                Usuarios U2 ON FIND_IN_SET(U2.id, P.equipo2)
                            WHERE 
                                FIND_IN_SET(:id, P.equipo1) > 0 OR FIND_IN_SET(:id, P.equipo2) > 0
                            GROUP BY 
                                P.id, P.ganador, P.equipo1, P.equipo2
                            ORDER BY 
                                P.id;
                        ");
                        
            $this->db->bind(':id', $id_usuario);


            return $this->db->registros();
        }
        

    }