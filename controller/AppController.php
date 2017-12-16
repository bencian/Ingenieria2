<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */
class AppController {
    
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

    public function validarLogin($post){
        if(($post["nomUsr"] == "") || ($post["psw"] == "")){
            $view = new Home();
            $view -> errorLogin("true");
        }elseif (AppModel::getInstance()->validateLogin($post)){
            self::getInstance()->menu();
        }else {
            $view = new Home();
            $view -> errorLogin("true");
        }

    }

    public function menu(){
        $view = new Home();
        $view->show("menu.html.twig");      
    }
    
    public function cargarFormPN(){
        $view = new Home();
        $view->show("formPN.html.twig"); 
    }

    public function validarPN($post){
        if(($post["nomYape"] == "") || ($post["direccion"] == "") || ($post["numero"] == "") || ($post["carta"] == "") || (isNan($post["tipoDoc"]))){
            $view = new Home();
            $view->errorForm("true"); 
            //falta la revalidacion del dni
        }elseif(AppModel::getInstance()->insertarPedido($post)){
            alert("su pedido fue realizado con exito");
            self::getInstance()->menu();
        }
        }
    }
}
