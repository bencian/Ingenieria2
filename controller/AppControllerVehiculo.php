<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');

require_once('controller/AppControllerViajes.php');
require_once('controller/AppControllerUsuario.php');
require_once('controller/AppController.php');

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
                AppController::getInstance()->mostrarMenuConSesion();
            } else {
                $this->registrar_vehiculo();
            }
        }
    }

    public function validar_vehiculo($datos){
        $valor = true;
        if(!preg_match("/^([A-Za-z]{2}[0-9]{3}[A-Za-z]{2}|[A-Za-z]{3}[0-9]{3})$/", $datos["patente"])){
            echo "La patente ingresada no es valida";
            $valor = false;
        }
        if(!preg_match("/^[1-2][0-9]{3}$/", $datos["modelo"])){
            echo "El modelo ingresado no es valido, ingrese el año de su vehiculo";
            $valor = false;
        }
        return $valor;
    }

    public function listar_vehiculos(){
        $view = new Home();
        $vehiculos = AppModel::getInstance()->getVehiculos(); 
        $view->listarVehiculosPropios($vehiculos); //falta
    }

    public function eliminar_vehiculo($datos){
            $this->confirmarEliminacionCascada($datos);
    }

    public function confirmar_eliminacion_en_cascada($datos){
        $bdViaje = AppModelViaje::getInstance();
        $bd = AppModel::getInstance();
        $bdViaje->eliminarViajesFuturosEnCascada($datos);
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
        $cant_viajes=$bdViaje->viajesConAceptados($datos);
        $vehiculo=AppModel::getInstance()->getVehiculo($datos["id"]);
        if(isset($cant_viajes[0]["COUNT(vj.id)"])){
            $parametros["viajes"]=$cant_viajes[0]["COUNT(vj.id)"];
        } else {
            $parametros["viajes"]=0;
        }
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
                AppController::getInstance()->mostrarMenuConSesion();
            } else {
                $vehiculos=$bd->getVehiculos();
                $view->listarVehiculosPropios($vehiculos); //falta
            }
        }
    }

    public function vehiculoViaja($datos){
        $puede = false;
        $bd = AppModel::getInstance();
        $viaje = AppModelViaje::getInstance();
        $patente = $bd->getPatente($datos["vehiculo"]);
        $viajesVehiculo = $viaje->getViajesConPatenteFecha($patente[0][0]);
        $viajesConflicto = 0;
        foreach (array_keys($viajesVehiculo) as $idViaje){
            if($this->viajeHorario($viajesVehiculo[$idViaje][0],$datos)){
                $viajesConflicto++;
            }
        }
        if($viajesConflicto == 0){
            $puede = true;
        }
        return $puede;
    }

    public function viajeHorario($datosViaje,$datosViajeNuevo){
        date_default_timezone_set("America/Argentina/Buenos_Aires");
        $datetime = new DateTime();
        $noPuede = false;
        $horario = AppModelViaje::getInstance()->getHorariosViaje($datosViaje);
        $horaInicialNueva = $datosViajeNuevo["fecha"]." ".$datosViajeNuevo["hora_salida"];
        $horaInicialAnterior = $horario[0]["fecha"]." ".$horario[0]["hora_salida"];
        $viaje_existente_hora_ini = $datetime->createFromFormat('Y-m-d H:i',$horaInicialAnterior);
        $viaje_existente_hora_fin = $datetime->createFromFormat('Y-m-d H:i',$horaInicialAnterior);
        $viaje_existente_hora_fin = $viaje_existente_hora_fin->add(new DateInterval('PT'.$horario[0]["duracion"].'H'));
        $viaje_nuevo_hora_ini = $datetime->createFromFormat('Y-m-d H:i',$horaInicialNueva);
        $viaje_nuevo_hora_fin = $datetime->createFromFormat('Y-m-d H:i',$horaInicialNueva);
        $viaje_nuevo_hora_fin = $viaje_nuevo_hora_fin->add(new DateInterval('PT'.$datosViajeNuevo["duracion"].'H'));        
        if($this->enUnIntervalo($viaje_nuevo_hora_ini,$viaje_existente_hora_ini,$viaje_existente_hora_fin)){
            $noPuede = true;
        }
        if($this->enUnIntervalo($viaje_nuevo_hora_fin,$viaje_existente_hora_ini,$viaje_existente_hora_fin)){
            $noPuede = true;
        }
        if($this->enUnIntervalo($viaje_existente_hora_ini,$viaje_nuevo_hora_ini,$viaje_nuevo_hora_fin)){
            $noPuede = true;
        }
        if($this->enUnIntervalo($viaje_existente_hora_fin,$viaje_nuevo_hora_ini,$viaje_nuevo_hora_fin)){
            $noPuede = true;
        }  
        return $noPuede;
    }

    public function enUnIntervalo($tiempo,$bordeInferior,$bordeSuperior){
        $adentro = false;
        if($tiempo->getTimestamp()>=$bordeInferior->getTimestamp()&&$tiempo->getTimestamp()<=$bordeSuperior->getTimestamp()){
            $adentro = true;
        }
        return $adentro;
    }
}