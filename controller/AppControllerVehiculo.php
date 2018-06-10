<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');

require_once('controller/AppControllerViajes.php');
require_once('controller/AppControllerUsuario.php');


class AppControllerVehiculo {
    
    private static $instance;

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    private function __construct() {
        
    }

    public function registrar_vehiculo(){
        $view = new Home();
        $bd = AppModel::getInstance();
        $tipos = $bd->tipos();
        $vector = array();
        for ($i=0;$i<count($tipos);$i++){
            $vector[$i]["nombre"]= $tipos[$i]["nombre"];
            $vector[$i]["id"]= $tipos[$i]["id"];
        }
        $string="registrar_vehiculo.html.twig";
        $view->formularioTipoVehiculos($vector,$string); //falta
    }

}