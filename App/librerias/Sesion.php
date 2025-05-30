<?php

class Sesion {

    public static function crearSesion($usuarioSesion) {
        $tiempoSesion = 365 * 24 * 60 * 60;
        session_set_cookie_params($tiempoSesion);

        session_regenerate_id();
        $_SESSION["usuarioSesion"] = $usuarioSesion;
        
    }

    public static function sesionCreada(&$datos = []) {
        if (isset($_SESSION["usuarioSesion"])) {
            $datos['usuarioSesion'] = $_SESSION["usuarioSesion"];
            return true;
        } else {
            return false;
        }
    }

    public static function cerrarSesion() {
        // Eliminar todas las variables de sesión
        $_SESSION = [];

        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destruir la sesión
        session_destroy();
    }

    public static function IniciarSesion(&$datos=[]){
        session_start();
        if(isset($_SESSION['usuarioSesion']) && in_array($_SESSION['rol'], $datos['roles_permitidos'])){
            $datos['usuarioSesion']=$_SESSION['usuarioSesion'];
            return true;
        }else{
            session_destroy();
            if($_SERVER['REQUEST_URI'] !== '/'){
                header("Location: /");
                exit();
            }
        }
   }
}
?>
