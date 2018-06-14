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

    public function crear_vehiculo($datos){
        $bd = AppModel::getInstance();
        $view = new Home();
        if(isset($datos)){
            $test = $this->validar_vehiculo($datos); 
            if(($bd->existeTipo($datos["tipo"]))&&($test)&&preg_match("#[1-9][0-9]?#",$datos["asientos"])){
                $datos["id_usuario"] = $_SESSION['id'];
                $bd->registrar_vehiculo($datos);
                $this->mostrarMenuConSesion();
            } else {
                $this->registrar_vehiculo();
            }
        }
    }

    public function validar_vehiculo($datos){
        $valor = ((preg_match("#[A-Za-z]{2}[0-9]{3}[A-Za-z]{2}|[A-Za-z]{3}[0-9]{3}#", $datos["patente"])) && (preg_match("#[1-2][0-9]{3}#", $datos["modelo"])));
        return $valor;
    }

    public function listar_vehiculos(){
        $view = new Home();
        $vehiculos = AppModel::getInstance()->getVehiculos(); 
        $view->listarVehiculosPropios($vehiculos); //falta
    }

    public function eliminar_vehiculo($datos){
        $bdViaje = AppModelViaje::getInstance();
        $bd = AppModel::getInstance();
        $tieneViajes=$bdViaje->noPoseeViajesFuturos($datos);
/*
aca no va false
*/      if(false){

          $viajes=$bdViaje->poseeViajesEchos($datos);
          if(!$viajes){
              //borrar de usuarios_has_vehiculo y de vehiculos
              $bd->borrarVehiculo($datos);
          }
          $bd->eliminarRelacionUsuarioVehiculo($datos);
          $view = new Home();
          $this->listar_vehiculos();
        }
        $this->confirmarEliminacionCascada($datos);
    }

    public function confirmar_eliminacion_en_cascada($datos){
        $bdViaje = AppModelViaje::getInstance();
        $bd = AppModel::getInstance();
        $bdViaje->eliminarViajesFuturosEnCascada($datos);
        /*$viajes=$bdViaje->poseeViajesEchos($datos);
        if(!$viajes){
            $bd->borrarVehiculo($datos);
        }
        $bd->eliminarRelacionUsuarioVehiculo($datos);*/
        //$view = new Home();
        $viajes=$bdViaje->poseeViajesEchos($datos);
        if(!$viajes){
              //borrar de usuarios_has_vehiculo y de vehiculos
              $bd->borrarVehiculo($datos);
        }
        $bd->eliminarRelacionUsuarioVehiculo($datos);
        $this->listar_vehiculos();
    }

    public function confirmarEliminacionCascada($datos){
        $view = new Home();
        $bdViaje = AppModelViaje::getInstance();
        $cant_viajes=$bdViaje->noPoseeViajesFuturos($datos);
        $vehiculo=AppModel::getInstance()->getVehiculo($datos["id"]);
        $parametros["viajes"]=$cant_viajes;
        $parametros["vehiculo"]=$vehiculo[0]["id"];
        $view->eliminarEnCascada($parametros);

    }


    public function modificar_vehiculo($datos){
        //datos tiene el id del vehiculo a modificar en un string
        $view = new Home();
        $bd = AppModel::getInstance();
        $idVehiculo = (int)$datos["id"];
        $vehiculo = $bd->getVehiculo($idVehiculo);
        $tipos = $bd->tipos();
        for ($i=1;$i<=count($tipos);$i++){
            $vehiculo[$i]["nombre"]= $tipos[$i-1]["nombre"];
            $vehiculo[$i]["id"]= $tipos[$i-1]["id"];
        }
        $string="modificar_vehiculo.html.twig";
        $view->modificarVehiculo($string,$vehiculo); //falta
    }

    public function actualizar_vehiculo($datos){
        $bd = AppModel::getInstance();
        $view = new Home();
        if(isset($datos)){
            $test = $this->validar_vehiculo($datos);
            if(($bd->existeTipo($datos["tipo"]))&&($test)&&preg_match("#[1-9][0-9]?#",$datos["asientos"])){
                $datos["id"] = (int)$_POST["id"];
                $datos["tipo"] = (int)$datos["tipo"];
                $bd->actualizar_vehiculo($datos);
                $this->mostrarMenuConSesion();
            } else {
                $vehiculos=$bd->getVehiculos();
                $view->listarVehiculosPropios($vehiculos); //falta
            }
        }
    }
}