<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');


class AppControllerViajes {
    
    private static $instance;

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    private function __construct() {
        
    }
    
    public function listadoViajesGenerales(){

        //CONTROLA QUE EL VIAJE SEA EN LOS PROXIMOS 30 DIAS!
        $diaMax = date('Y-m-d', strtotime("+30 days"));
        $viajesVar = AppModelViajes::getInstance()->getViajes($diaMax);
        $parametros = array();
        if(count($viajesVar) == 0){
            $parametros['mensaje'] = 'No hay viajes registrados.';
        }else{
            $parametros['listaViajes'] = $viajesVar;
            $parametros['mensaje'] = 'Listado de viajes.';
        }
        return $parametros;
    }

}