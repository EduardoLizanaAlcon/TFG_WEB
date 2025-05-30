<?php
/*DESARROLLO*/
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
/*DESARROLLO*/


    //Ruta de la aplicacion
    define('RUTA_APP', dirname(dirname(__FILE__)));

    //Ruta de la aplicacion
    define('RUTA_PUBLIC_CSS', 'css/');
    define('RUTA_PUBLIC_JS', 'js/');


    //Ruta url
    define('RUTA_URL', 'http://162.19.224.30/');

    //
    define('NOMBRE_SITIO', 'CRUD MVC - DAM2 Enlaces');
 
    define('DB_HOST', 'localhost');
    define('DB_USUARIO', 'TFG');
    define('DB_PASSWORD', 'Eduardo_123');
    define('DB_NOMBRE', 'TFG');
    
    //Tamaño paginacion.
    define('TAM_PAGINA', 2);
