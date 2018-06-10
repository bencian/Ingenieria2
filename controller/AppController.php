<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');

require_once('controller/AppControllerViajes.php');
require_once('controller/AppControllerUsuario.php');


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
    
   public function index(){
        if(!isset($_SESSION['id'])){
			$view = new Home();
			$viajes = $this->accesoAPaginaQueLista();
			$parametros['ciudades'] = AppModel::getInstance()->getCiudades();
			$view->mostrarMenuSinSesion("index.html.twig", $viajes,$parametros);
		} else {
			$this->mostrarMenuConSesion();  
		}
	}
    
    public function mostrarMenuConSesion(){
        $bd = AppModel::getInstance();
        $view = new Home();
        $ciudades = $bd->getCiudades();
        $vectorFormulario["ciudades"] = $ciudades;
        $vehiculosUsuario = $bd->getVehiculos();
        $vectorFormulario["vehiculos"] = $vehiculosUsuario;
        $viajes = $this->accesoAPaginaQueLista();
        $view->listarCiudadesMenuPrincipal($vectorFormulario, $viajes); //falta
    }

    public function accesoAPaginaQueLista(){
        //Muestra la el menu principal con el listado de viajes
        $controller = AppControllerViajes::getInstance();
        $parametros = $controller->listadoViajesGenerales();
        $arreglo = array();
        $arreglo['mensajeDeResultado'] = $parametros['mensaje'];
        if(isset($parametros['listaViajes'])){
            $arreglo['listadoCompletoDeViajes'] = $parametros['listaViajes'];
            $arreglo['elemPorPagina'] = 3;
        }
        return $arreglo;
    }
}