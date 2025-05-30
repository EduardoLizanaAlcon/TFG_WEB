<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 86400");
header("Access-Control-Expose-Headers: X-Custom-Header");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

    //Cargamo librerias
    require_once "config/configurar.php";
    require_once "helpers/funciones.php";
    
    require_once "librerias/Base.php";
    require_once "librerias/Controlador.php";
    require_once "librerias/Core.php";
    require_once "librerias/Sesion.php";
