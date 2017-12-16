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
                session_start();
                $_SESSION["nombreUsr"] = $post["nomUsr"];
                $view = new Home();
                $view->show("menu.html.twig");
            }else {
                $view = new Home();
                $view -> errorLogin("true");
            }
    }

    public function menu(){
        if(self::getInstance()->checkPermission()){
        $view = new Home();
        $view->show("menu.html.twig");
        }      
    }
    
    public function cargarFormPN(){
        if(self::getInstance()->checkPermission()){
        $view = new Home();
        $view->show("formPN.html.twig");
        } 
    }

    public function validarPN($post){
        if(self::getInstance()->checkPermission()){
        if(($post["nombrePN"] == "") || ($post["direccion"] == "") || ($post["numero"] == "") || ($post["carta"] == "") || (is_nan($post["tipoDoc"]))){
            $view = new Home();
            $view->errorForm("true"); 
        }else{
            AppModel::getInstance()->insertarPedido($post);
            alert("su pedido fue realizado con exito");
            self::getInstance()->menu();
        }
    }
    }

    public function checkPermission (){
        if(!isset($_SESSION["nombreUsr"])){
            $view = new Home();
            $view->show("login.html.twig");
        }
    }
    public function listarPedidos (){
        if(self::getInstance()->checkPermission()){
        AppModel::getInstance()->traerPedidos();
    }
    
}
}
