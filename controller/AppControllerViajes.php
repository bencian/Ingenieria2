<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');
require_once('model/AppModelViaje.php');
require_once('controller/AppControllerUsuario.php');
require_once('controller/AppController.php');

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
        $viajesVar = AppModelViaje::getInstance()->getViajes($diaMax);
        $parametros = array();
        if(count($viajesVar) == 0){
            $parametros['mensaje'] = 'No hay viajes registrados.';
        }else{
            $parametros['listaViajes'] = $viajesVar;
            $parametros['mensaje'] = 'Listado de viajes.';
        }
        return $parametros;
    }

    public function buscador($datos){
        $view = new Home();
        $viajes= AppModelViaje::getInstance();
        if(isset($datos["origen"]) && isset($datos["salida"]) && (($datos["origen"]!="")&& $datos["salida"]!="")){
            if(isset($datos["destino"]) && $datos["destino"]!=""){
                $viajes_hechos=$viajes->busqueda_completa($datos);
            } else {
                $viajes_hechos=$viajes->busqueda_parcial($datos);
            }
            $ciudades= AppModel::getInstance()->getCiudades();
            $view->listarViajes($viajes_hechos, $ciudades); //falta
        } else {
            echo "Faltan ingresar datos";
        }
    }

    public function eliminarViaje($idViaje){
        $this->eliminarViajeDeLaBD($idViaje);
        Echo "el viaje se eliminó con exito";
        AppControllerUsuario::getInstance()->mostrarPerfil();
    }

    public function eliminarViajeDeLaBD($idViaje){  
        $bd = AppModelViaje::getInstance();
        $bd->eliminarViajeOcasional($idViaje);
        $bd->eliminarViajePeriodicoDias($idViaje);
        $bd->eliminarViajePeriodico($idViaje);
        $bd->eliminarViaje($idViaje);
    }

    public function modificar_viaje_ocasional($datos){
        $view = new Home();
        $bd = AppModel::getInstance();
        $ciudades = $bd->getCiudades();
        $vectorFormulario["ciudades"] = $ciudades;
        $vehiculosUsuario = $bd->getVehiculos();
        $vectorFormulario["vehiculos"] = $vehiculosUsuario;
        $viaje = AppModelViaje::getInstance()->getViajeOcasional($datos);        
        $view->modificarViajeOcasional($viaje, $vectorFormulario); //falta
    }

    public function publicarViajeOcasional($datos){
        if($this->validarViajeOcasional($datos)){
            $bd = AppModelViaje::getInstance();
            $asientos = AppModel::getInstance()->getAsientos($datos["vehiculo"]);
            $datos["asientos"] = $asientos[0]["asientos"];
            $idViaje = $bd->getViajeId($datos);
            $datos["id_viaje"] = $idViaje;
            $bd->crearOcasional($datos);
        }
        AppController::getInstance()->mostrarMenuConSesion();       
    }

    public function validarViajeOcasional($datos){
        $tempArray = explode('-',$datos["fecha"]);
        for ($i=0;$i<count($tempArray);$i++){
            $tempArray[$i] = (int)$tempArray[$i];
        }
        $entra = true;
        if(!$this->fechaMayor($tempArray)){
            echo "Fecha ingresada invalida";
            $entra = false;
        }
        if(!$this->esNumerico($datos["precio"])){
            echo "El precio no puede tener letras";
            $entra = false;
        }
        if(!$this->esNumerico($datos["duracion"])){
            echo "La duracion no puede tener letras";
            $entra = false;
        }
        if($datos["origen"]==$datos["destino"]){
            echo "El origen y el destino no pueden ser los mismos";
            $entra = false;
        }
        if(!$this->esNumerico($datos["distancia"])){
            echo "La distancia no puede tener letras";
            $entra = false;
        }
        if($this->esHoy($tempArray)){
            if($this->masTarde($datos["hora_salida"])){
                echo "Debe ser para mas tarde";
                $entra = false;
            }
        }
        return $entra;
    }

    public function fechaMayor($vectorFecha){
        date_default_timezone_set("America/Argentina/Buenos_Aires");
        $anio = (int) date('y')+2000;
        $mes = (int) date('m');
        $dia = (int) date('d');
        if($vectorFecha[0]<$anio){
            return false;
        } else {
            if($vectorFecha[0]==$anio){
                if($vectorFecha[1]<$mes){
                    return false;
                } else {
                    if($vectorFecha[1]==$mes){
                        if($vectorFecha[2]<$dia){
                            return false;
                        } else {
                            return true;
                        }
                    } else {
                        return true;
                    }
                }
            } else {
                return true;
            }
        }
    }
    
    public function esHoy($vectorFecha){
        date_default_timezone_set("America/Argentina/Buenos_Aires");
        $anio = (int) date('y')+2000;
        $mes = (int) date('m');
        $dia = (int) date('d');
        if (($anio == $vectorFecha[0])&&($mes == $vectorFecha[1])&&($dia == $vectorFecha[2])){
            return true;
        } else {
            return false;
        }
    }
    
    public function esNumerico($string){
        return preg_match("/^[0-9]*$/",$string);
    }
    
    public function masTarde($hora){
        date_default_timezone_set("America/Argentina/Buenos_Aires");
        $tempArray = explode(':',$hora);
        for ($i=0;$i<count($tempArray);$i++){
            $tempArray[$i] = (int)$tempArray[$i];
        }
        $hours = (int)date("G");
        $minutes = (int)date("i");
        if($tempArray[0]>=$hours){
            if($tempArray[0]==$hours){
                if ($tempArray[1]>$minutes){
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    public function publicarViajePeriodico($datos){
        $test = $this->validarViajePeriodico($datos);
        if($test){  
            $bd = AppModel::getInstance();
            $bdViaje = AppModelViaje::getInstance();
            $asientos = $bd->getAsientos($datos["vehiculo"]);
            $datos["asientos"] = $asientos[0]["asientos"];
            $fechas = $this->diasViajePeriodico($datos);            
            $vectorFechas = $this->acomodarVectorFechas($fechas,$datos);
            foreach(array_keys($vectorFechas) as $fecha){
                $datos["fecha"] = $fecha;
                $datosPeriodico["viajeId"] = $bdViaje->getViajeId($datos);
                $datosPeriodico["fechaFinal"] = $fecha;
                $datosDiaHorario["horario"] = $vectorFechas[$fecha];
                $datosDiaHorario["fecha"] = $datosPeriodico["fechaFinal"];
                $datosDiaHorario["idViaje"] = $datosPeriodico["viajeId"];
                $bdViaje->asociarPeriodico($datosPeriodico);
                $bdViaje->asociarDiaHorario($datosDiaHorario);
            }
        }
        AppController::getInstance()->mostrarMenuConSesion();     
    }
    
    public function validarViajePeriodico($datos){
        $tempArray = explode('-',$datos["fecha"]);
        for ($i=0;$i<count($tempArray);$i++){
            $tempArray[$i] = (int)$tempArray[$i];
        }
        $entra = true;
        if(!$this->fechaMayor($tempArray)){
            echo "Fecha ingresada invalida";
            $entra = false;
        }
        if(!$this->esNumerico($datos["precio"])){
            echo "El precio no puede tener letras";
            $entra = false;
        }
        if(!$this->esNumerico($datos["duracion"])){
            echo "La duracion no puede tener letras";
            $entra = false;
        }
        if($datos["origen"]==$datos["destino"]){
            echo "El origen y el destino no pueden ser los mismos";
            $entra = false;
        }
        if(!$this->esNumerico($datos["distancia"])){
            echo "La distancia no puede tener letras";
            $entra = false;
        }
        return $entra;
    }
    
    public function acomodarVectorFechas($vector,$datos){
        if($datos["hora_lunes"] != ""){
            $vector[$vector[1]->format('Y-m-d')] = $datos["hora_lunes"];
        }
        unset($vector[1]);
        if($datos["hora_martes"] != ""){
            $vector[$vector[2]->format('Y-m-d')] = $datos["hora_martes"];
        }
        unset($vector[2]);
        if($datos["hora_miercoles"] != ""){
            $vector[$vector[3]->format('Y-m-d')] = $datos["hora_miercoles"];
        }
        unset($vector[3]);
        if($datos["hora_jueves"] != ""){
            $vector[$vector[4]->format('Y-m-d')] = $datos["hora_jueves"];
        }
        unset($vector[4]);
        if($datos["hora_viernes"] != ""){
            $vector[$vector[5]->format('Y-m-d')] = $datos["hora_viernes"];
        }
        unset($vector[5]);
        if($datos["hora_sabado"] != ""){
            $vector[$vector[6]->format('Y-m-d')] = $datos["hora_sabado"];
        }
        unset($vector[6]);
        if($datos["hora_domingo"] != ""){
            $vector[$vector[0]->format('Y-m-d')] = $datos["hora_domingo"];
        }
        unset($vector[0]);
        return $vector;
    }
    
    public function diasViajePeriodico($datos){
        if($this->esHoy($datos["fecha"])){
            $numDia = date('w',strtotime($fechaInicial));
            switch ($numDia){
                case "0":
                    if($datos["hora_domingo"]=!""){
                        //controlar horario, crear viaje para hoy o doming que viene
                        if($this->masTarde($datos["hora_domingo"])){
                            $vector[0]= $datos["fecha"];
                        } else {
                            $date = new DateTime($datos["fecha"]);
                            $date->modify('next Sunday');
                            $vector[0]= $date;
                        }
                    } 
                    $datos["hora_domingo"] = "";
                break;
                case "1":
                    if($datos["hora_lunes"]=!""){
                        //controlar horario, crear viaje para hoy o lunes que viene
                        if($this->masTarde($datos["hora_lunes"])){
                            $vector[1]= $datos["fecha"];
                        } else {
                            $date = new DateTime($datos["fecha"]);
                            $date->modify('next Monday');
                            $vector[1]= $date;
                        }
                    }
                    $datos["hora_lunes"] = "";
                break;
                case "2":
                    if($datos["hora_martes"]=!""){
                        //controlar horario, crear viaje para hoy o martes que viene
                        if($this->masTarde($datos["hora_martes"])){
                            $vector[2]= $datos["fecha"];
                        } else {
                            $date = new DateTime($datos["fecha"]);
                            $date->modify('next Tuesday');
                            $vector[2]= $date;
                        }
                    }
                    $datos["hora_martes"] = "";
                break;
                case "3":
                    if($datos["hora_miercoles"]=!""){
                        //controlar horario, crear viaje para hoy o mier que viene
                        if($this->masTarde($datos["hora_miercoles"])){
                            $vector[3]= $datos["fecha"];
                        } else {
                            $date = new DateTime($datos["fecha"]);
                            $date->modify('next Wednesday');
                            $vector[3]= $date;
                        }
                    }
                    $datos["hora_miercoles"]="";
                break;
                case "4":
                    if($datos["hora_jueves"]=!""){
                        //controlar horario, crear viaje para hoy o jueves que viene
                        if($this->masTarde($datos["hora_jueves"])){
                            $vector[4]= $datos["fecha"];
                        } else {
                            $date = new DateTime($datos["fecha"]);
                            $date->modify('next Thursday');
                            $vector[4]= $date;
                        }
                    }
                    $datos["hora_jueves"]="";
                break;
                case "5":
                    if($datos["hora_viernes"]=!""){
                        //controlar horario, crear viaje para hoy o viernes que viene
                        if($this->masTarde($datos["hora_viernes"])){
                            $vector[5]= $datos["fecha"];
                        } else {
                            $date = new DateTime($datos["fecha"]);
                            $date->modify('next Friday');
                            $vector[5]= $date;
                        }
                    }
                    $datos["hora_viernes"]="";
                break;
                case "6":
                    if($datos["hora_sabado"]=!""){
                        //controlar horario, crear viaje para hoy o sabado que viene
                        if($this->masTarde($datos["hora_sabado"])){
                            $vector[6]= $datos["fecha"];
                        } else {
                            $date = new DateTime($datos["fecha"]);
                            $date->modify('next Saturday');
                            $vector[6]= $date;
                        }
                    }
                    $datos["hora_sabado"]="";
                break;
            }
        }
        if($datos["hora_lunes"]=!""){
            //viaje el lunes
            $date = new DateTime($datos["fecha"]);
            $date->modify('next Monday');
            $vector[1]= $date;
        }
        if($datos["hora_martes"]=!""){
            //viaje el martes
            $date = new DateTime($datos["fecha"]);
            $date->modify('next Tuesday');
            $vector[2]= $date;
        }
        if($datos["hora_miercoles"]=!""){
            //viaje el miercoles
            $date = new DateTime($datos["fecha"]);
            $date->modify('next Wednesday');
            $vector[3]= $date;
        }
        if($datos["hora_jueves"]=!""){
            //viaje el jueves
            $date = new DateTime($datos["fecha"]);
            $date->modify('next Thursday');
            $vector[4]= $date;
        }
        if($datos["hora_viernes"]=!""){
            //viaje el viernes
            $date = new DateTime($datos["fecha"]);
            $date->modify('next Friday');
            $vector[5]= $date;
        }
        if($datos["hora_sabado"]=!""){
            //viaje el sabado
            $date = new DateTime($datos["fecha"]);
            $date->modify('next Saturday');
            $vector[6]= $date;
        }
        if($datos["hora_domingo"]=!""){
            //viaje el domingo
            $date = new DateTime($datos["fecha"]);
            $date->modify('next Sunday');
            $vector[0]= $date;
        }
        return $vector;
    }

    public function ver_publicacion_viaje($viaje_id){
        $view=new Home();
        $model=AppModel::getInstance();
        $dbViaje=AppModelViaje::getInstance();
        $viaje=$dbViaje->getViaje($viaje_id);
        $calificaciones=$model->getCalificaciones();
        $vehiculo=($model->getVehiculo($viaje["viaje"]["vehiculo_id"]))[0];
        $ciudades=$model->getCiudades();
        $piloto=(AppModelUsuario::getInstance()->getPerfil($viaje["viaje"]["usuarios_id"]))[0];
        if(isset($_SESSION["id"])){
            $postulado=$dbViaje->yaMePostule($viaje_id);
            $postulados=$dbViaje->getPostulados($viaje_id);
            $view->verPublicacionViaje($viaje,$calificaciones,$vehiculo,$ciudades, $piloto, $postulado, $postulados);
        } else {
            $view->verPublicacionViaje($viaje,$calificaciones,$vehiculo,$ciudades, $piloto, '', '');
            /*

            REVISAR COMO TRATA EL STRING VACIO!!! CREO QUE ESTA FUNCIONANDO POR EL IF EN EL VIEW, QUE HACE QUE COMO SESSION NO ESTA SETEADO NO ENVIA $POSTULADO COMO PARAMETRO!

            */
        }
    }

    public function postularse($datos){
        if(!(AppModelViaje::getInstance()->yaMePostule($datos))){
            AppModelViaje::getInstance()->postularme($datos);/*
            AppController::getInstance()->mostrarMenuConSesion();*/
        }
        $this->ver_publicacion_viaje($datos);
    }

    public function cancelar_postulacion($datos){
        AppModelViaje::getInstance()->cancelarPostulacion($datos);
        $this->ver_publicacion_viaje($datos);
    }

    public function modificarViajeOcasional($datos){
        $view = new Home(); //CREO QUE ESTE NO VA!
        /*$viaje= $bd->getViajeOcasional($datos["id"]);*/
        $valido=$this->validarViajeOcasional($datos);
        if($valido){
            $asientos=AppModel::getInstance()->getAsientos($datos["vehiculo"]);
            $db = AppModelViaje::getInstance();
            $db-> actualizarViajeOcasional($datos,$asientos);
        }
        AppController::getInstance()->mostrarMenuConSesion();    
    }

    public function cancelar_postulacion_aceptada($datos){
        $view = new Home();
        $view->cancelarPostulacionAceptada($datos);
    }

    public function borrar_postulacion_aceptada($datos){
        AppModelViaje::getInstance()->cancelarPostulacion($datos);
        /*

        ACA HAY QUE DESCONTAR LOS PUNTOS PERO NO ESTA LA PUNTUACION

        */
        $this->ver_publicacion_viaje($datos);
    }
}