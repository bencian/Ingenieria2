<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');


class AppControllerUsuario {
    
    private static $instance;

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    private function __construct() {
        
    }

    public function login(){
        $view = new Home();
        $view->show("login.html.twig");
    }
    
    public function registrarse(){
        $view = new Home();
        $view->show("registrarse.html.twig");
    }

    public function crear_usuario($datos){
        //agrega el usuario en la bd
        $bdUsuario = AppModelUsuario::getInstance();
        $view = new Home();
        if(isset($datos)){
            $test = $this->validacionUsuario($datos); //falta
            if(!($bdUsuario->existeMail($datos["email"]))&&($test)){
                $bdUsuario->registrar($datos);
                $view->show("index.html.twig"); //sospechoso!
            } else {
                if ($bdUsuario->existeMail($datos["email"])){
                    echo "Ya existe el mail en la base de datos ";
                }
                $view->show("registrarse.html.twig"); //falta
            }
        }           
    }
}