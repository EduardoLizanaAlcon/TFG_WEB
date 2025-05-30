<?php
class UserControlador extends Controlador {
  
        
    var $modeloUsuario;
    public $datos;

    
    public function __construct(){
        $this->modeloUsuario = $this->modelo('SesionModelo');

    }
    public function index(){
    }

    public function login() {
        // Leer el JSON desde el cuerpo de la solicitud POST
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true); // Convertir a array asociativo

        // Validar que se hayan recibido los datos esperados
        if (!isset($datos['usuario']) || !isset($datos['password'])) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Faltan datos: usuario o password'
            ];
            $this->vistaApi($this->datos);
            return;
        }

        // Obtener los valores del array
        $usuario = $datos['usuario'];
        $password = $datos['password'];

        // Verificar credenciales (ajusta según tu modelo)
        $usuario = $this->modeloUsuario->comprobacion($usuario, $password);

        if ($usuario) {
            $this->datos = [
                'success' => true,
                'mensaje' => 'Usuario o Contraseña Correctos',
                'usuario' => $usuario
            ];
        } else {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Usuario o Contraseña incorrectos',
                'usuario' => []
            ];
        }

        // Enviar respuesta
        $this->vistaApi($this->datos);

    }


    public function registrarUsuario() {
        // Leer el JSON desde el cuerpo de la solicitud POST
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true); // Convertir a array asociativo
    
        // Validar que se hayan recibido los datos esperados
        if (
            !isset($datos['usuario']) || 
            !isset($datos['contrasena']) || 
            !isset($datos['nombre']) || 
            !isset($datos['apellido']) || 
            !isset($datos['num_tel'])
        ) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Faltan datos: usuario, contrasena, nombre, apellido o num_tel'
            ];
            $this->vistaApi($this->datos);
            return;
        }
    
        // Obtener los valores del array
        $usuario = $datos['usuario'];
        $contrasena = $datos['contrasena'];
        $nombre = $datos['nombre'];
        $apellido = $datos['apellido'];
        $num_tel = $datos['num_tel'];
    
        // Registrar el usuario
        $this->datos = $this->modeloUsuario->registrarUsuario($usuario, $contrasena, $nombre, $apellido, $num_tel);
    
        // Enviar respuesta
        $this->vistaApi($this->datos);
    }
    
    public function editarPerfil() {
        // Leer el JSON desde el cuerpo de la solicitud POST
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true); // Convertir a array asociativo

        // Validar que se hayan recibido los datos esperados
        if (
            !isset($datos['id_usuario']) ||
            !isset($datos['usuario']) || 
            !isset($datos['nombre']) || 
            !isset($datos['apellido']) || 
            !isset($datos['num_tel'])
        ) {
            $this->datos = [
                'success' => false,
                'mensaje' => 'Faltan datos: id_usuario, usuario, nombre, apellido o num_tel'
            ];
            $this->vistaApi($this->datos);
            return;
        }

        // Obtener los valores del array
        $id_usuario = $datos['id_usuario'];
        $usuario = $datos['usuario'];
        $nombre = $datos['nombre'];
        $apellido = $datos['apellido'];
        $num_tel = $datos['num_tel'];

        // Actualizar el perfil
        $this->datos = $this->modeloUsuario->editarPerfil($id_usuario, $usuario, $nombre, $apellido, $num_tel);

        // Enviar respuesta
        $this->vistaApi($this->datos);
    }
    

    public function ObtenerRegistroUsuario() {
        try {
            $json = file_get_contents('php://input');
            $datos = json_decode($json, true);

            $resultado = $this->modeloUsuario->comprobarHistorico($datos['id_usuario']);

            if ($resultado) {
                $respuesta = [
                    'success' => true,
                    'mensaje' => '',
                    'partidas' => $resultado
                ];
            } else {
                $respuesta = [
                    'success' => false,
                    'mensaje' => 'No se encontraron registros para el usuario.',
                    'partidas' => []

                ];
            }
        } catch (Exception $e) {
            $respuesta = [
                'success' => false,
                'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage(),
                'partidas' => []

            ];
        }

        $this->vistaApi($respuesta);
    }

    


}
