<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');
require_once('model/AppModelUsuario.php');

require_once('controller/AppControllerViajes.php');
require_once('controller/AppControllerUsuario.php');
require_once('controller/AppControllerVehiculo.php');


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
        $view->listarCiudadesMenuPrincipal($vectorFormulario, $viajes);
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

    public function validar_Inicio_Sesion(){
        if(isset($_POST['usr'])&&isset($_POST['contraseña'])){   
            $datos['nombre_usuario'] = $_POST['usr'];
            $datos['contraseña_usuario'] = $_POST['contraseña'];
            $usuario = AppModelUsuario::getInstance()->existeUsuario($datos['nombre_usuario'],$datos['contraseña_usuario']);
                if (count($usuario) == 0){
                    //Aviso que no existe usuario o que no corresponde con su psw
                    $view = new Home();
                    $view->errorLogin("el usuario no corresponde con la contraseña");
                }else{
                    $vector_usuario = AppModelUsuario::getInstance()->getId($datos['nombre_usuario']);
                    $usuario_id = (int)$vector_usuario[0][0];
                    $_SESSION["id"]= $usuario_id;
                    $view = new Home();
                    $this->mostrarMenuConSesion();
            }
        }
    }

    public function cerrarSesion(){
        //Cierra sesion y accede al index
        if(isset($_SESSION)){
            session_unset();
            session_destroy();
            $this->index();
        }
    }
}