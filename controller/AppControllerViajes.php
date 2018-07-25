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
require_once('controller/AppControllerVehiculo.php');

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
        $viajes_hechos=0; //ESTO DESPUES HAY QUE BORRARLO CUANDO TENGAMOS PAGINA PARA ERRORES
        if(isset($datos["origen"]) && isset($datos["salida"]) && (($datos["origen"]!="")&& $datos["salida"]!="")){
            if(isset($datos["destino"]) && $datos["destino"]!=""){ /* podria ser -1 */
                if($datos["destino"]!=$datos["origen"]){
                    $viajes_hechos=$viajes->busqueda_completa($datos);
                } else {
                    $errno["distinto"]="No se puede publicar viajes donde el origen sea el mismo que el destino, asi que no va a haber resultados!";
                    $_SESSION["errno"]=$errno;
                    //echo("No se puede publicar viajes donde el origen sea el mismo que el destino, asi que no va a haber resultados!");
                }
            } else {
                $viajes_hechos=$viajes->busqueda_parcial($datos);
            }
            $ciudades= AppModel::getInstance()->getCiudades();
            $ciudadesOrdenadas=AppModel::getInstance()->getCiudadesOrdenadas();
            $view->listarViajes($viajes_hechos, $ciudades, $datos, $ciudadesOrdenadas); //falta
        } else {
            //echo "<h1>No juegues con la URL, hjo de una gran... de de muzzarella</h1>";
            $errno["buscador"]="No juegues con la URL, hjo de una gran... de de muzzarella";
            $_SESSION["errno"]=$errno;
            AppController::getInstance()->mostrarMenuConSesion();  
        }
    }

    public function eliminarViaje($idViaje){
        if(!$this->hayAceptados($idViaje)){
            $this->eliminarViajeDeLaBD($idViaje);
        }else{
            //aca va el eliminar en cascada pero por el momento elimina asi nomas...
            $this->eliminarViajeDeLaBD($idViaje);
        }
        AppControllerUsuario::getInstance()->mostrarPerfil("futuro");
    }

    public function hayAceptados($idViaje){
        $bd = AppModelViaje::getInstance();
        $aceptados = $bd->aceptadosParaEsteViaje($idViaje);
        if(count($aceptados) == 0){
            return false;
        }else{
            return true;
        }
    }

    public function eliminarViajeDeLaBD($idViaje){  
        $bd = AppModelViaje::getInstance();
        $bd->eliminarViajeOcasional($idViaje);
        $bd->eliminarViaje($idViaje);
    }

    public function modificar_viaje_ocasional($datos){
        $view = new Home();
        $bd = AppModel::getInstance();
        $ciudadesOrdenadas = $bd->getCiudadesOrdenadas();
        $vehiculosUsuario = $bd->getVehiculos();
        $vectorFormulario["vehiculos"] = $vehiculosUsuario;
        $viaje = AppModelViaje::getInstance()->getViajeOcasional($datos);        
        $view->modificarViajeOcasional($viaje, $vectorFormulario, $ciudadesOrdenadas); //falta
    }

    public function publicarViajeOcasional($datos){
        if($this->validarViajeOcasional($datos)){
            $this->publicar_viaje_ocasional($datos);
        }
        AppController::getInstance()->mostrarMenuConSesion();       
    }

    public function publicar_viaje_ocasional($datos){
        $bd = AppModelViaje::getInstance();
        $asientos = AppModel::getInstance()->getAsientos($datos["vehiculo"]);
        $datos["asientos"] = $asientos[0]["asientos"];
        $bd->getViajeId($datos);
        //$datos["id_viaje"] = $idViaje;
        //$bd->crearOcasional($datos);
    }

    public function validarViajeOcasional($datos){
        $tempArray = explode('-',$datos["fecha"]);
        for ($i=0;$i<count($tempArray);$i++){
            $tempArray[$i] = (int)$tempArray[$i];
        }
        $entra = true;
        $errno=array();
            
        if(!$this->fechaMayor($tempArray)){
            //echo "Fecha ingresada invalida";
            $errno["fechaMayor"]="Fecha ingresada invalida";
            $entra = false;
        }
        if(!$this->esNumerico($datos["precio"])){
            //echo "El precio no puede tener letras";
            $errno["precio"]="El precio no puede tener letras";
            $entra = false;
        }
        if(!$this->esNumerico($datos["duracion"])){
            //echo "La duracion no puede tener letras";
            $errno["duracion"]="La duracion no puede tener letras";
            $entra = false;
        }
        if($datos["origen"]==$datos["destino"]){
            //echo "El origen y el destino no pueden ser los mismos";
            $errno["origen"]="El origen y el destino no pueden ser los mismos";
            $entra = false;
        }
        if(!$this->esNumerico($datos["distancia"])){
            //echo "La distancia no puede tener letras";
            $errno["distancia"]="La distancia no puede tener letras";
            $entra = false;
        }
        if($this->esHoy($tempArray)){
            if($this->masTarde($datos["hora_salida"])){
                //echo "Debe ser para mas tarde";
                $errno["hora"]="Debe ser para mas tarde";
                $entra = false;
            }
        }
        if(!AppControllerVehiculo::getInstance()->vehiculoViaja($datos)){
            //echo "El vehiculo tiene un viaje para ese horario";
            $errno["viajes"]="El vehiculo tiene un viaje para ese horario";
            $entra = false;
        }
    
        $_SESSION["errno"]=$errno;
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
        $datetime = new DateTime();
        $horaInicio = $datetime->createFromFormat('H:i',$hora);
        if($horaInicio->getTimestamp()>=time()){
            return false;
        } else {
            return true;
        }
    }

    public function publicarViajePeriodico($datos){
        if($this->validarViajePeriodico($datos)){
            if($this->diasAViajar($datos)==0){
                echo "Debes seleccionar al menos un dia de la semana entre la fecha inicial y la final";
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
        if(strtotime($datos["fecha"])>strtotime($datos["fechaFinal"])){
            echo "Fecha inicial mayor a la fecha final";
            $entra = false;
        }   
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

    public function seViajaHoy($datos,$fecha){
        $tempArray = explode('-',$fecha);
        for ($i=0;$i<count($tempArray);$i++){
            $tempArray[$i] = (int)$tempArray[$i];
        }
        $numDia = date('w',strtotime($fecha));
        $datosEnviar = $datos;
        $datosEnviar["fecha"]=$fecha;
        switch ($numDia){
            case "0":
                $datosEnviar["hora_salida"] = $datos["hora_domingo"];
                $viaja = $this->cargarViaje($datosEnviar,$tempArray);
            break;
            case "1":
                $datosEnviar["hora_salida"] = $datos["hora_lunes"];
                $viaja = $this->cargarViaje($datosEnviar,$tempArray);    
            break;
            case "2":
                $datosEnviar["hora_salida"] = $datos["hora_martes"];
                $viaja = $this->cargarViaje($datosEnviar,$tempArray);
            break;
            case "3":
                $datosEnviar["hora_salida"] = $datos["hora_miercoles"];
                $viaja = $this->cargarViaje($datosEnviar,$tempArray);
            break;
            case "4":
                $datosEnviar["hora_salida"] = $datos["hora_jueves"];
                $viaja = $this->cargarViaje($datosEnviar,$tempArray);
            break;
            case "5":
                $datosEnviar["hora_salida"] = $datos["hora_viernes"];
                $viaja = $this->cargarViaje($datosEnviar,$tempArray);
            break;
            case "6":
                $datosEnviar["hora_salida"] = $datos["hora_sabado"];
                $viaja = $this->cargarViaje($datosEnviar,$tempArray);
            break;
        }
        return $viaja;
    }

    public function cargarViaje($datos,$tempArray){
        $viaja = false;
        if($datos["hora_salida"]!==""){    
            $vehiculoViaja = AppControllerVehiculo::getInstance()->vehiculoViaja($datos);
            if($vehiculoViaja){
                if($this->esHoy($tempArray)){
                    if(!$this->masTarde($datos["hora_salida"])){
                        //cargar viaje
                        $this->publicar_viaje_ocasional($datos);
                        $viaja = true;
                    } else {
                        echo "El viaje del dia ".$datos["fecha"]." debe ser para mas tarde";
                        $viaja = false;
                    }  
                } else {
                    //cargar viaje
                    $this->publicar_viaje_ocasional($datos);
                    $viaja = true;
                } 
            } else {
                echo "El vehiculo tiene un viaje programado para el dia ".$datos["fecha"]." ";
                $viaja = false;
            }
        }
        return $viaja;
    }

    public function diasAViajar($datos){
        $fechaInicial = strtotime($datos["fecha"]);
        $fechaFinal = strtotime($datos["fechaFinal"]);
        $dias = $fechaFinal - $fechaInicial;
        $cantDias = round($dias / (60*60*24));
        $iterador = 0;
        $diasConViaje = 0;
        $fechaInicial = $datos["fecha"];
        while ($iterador <= (int)$cantDias){
            //llama a la funcion que carga al viaje ocasional, que devuelve true o false dependiendo de si se viaja o no
            if($this->seViajaHoy($datos,$fechaInicial)){
                //aumenta la cantidad de dias viajados, para poder saber si no se puso ninguna fecha valida
                $diasConViaje++;
            }
            $iterador++;
            //aumentar $fechaInicial
            $date = date_create($fechaInicial);
            date_modify($date,'+1 day');
            $fechaInicial = date_format($date,"Y-m-d");
        }
        return $diasConViaje;
    }
    
    public function ver_publicacion_viaje($datos){
        $view=new Home();
        $model=AppModel::getInstance();
        $dbViaje=AppModelViaje::getInstance();
        $viaje=$dbViaje->getViaje($datos);
        $calificaciones=$model->getCalificaciones();
        $vehiculo=$model->getVehiculo($viaje["vehiculo_id"])[0];
        $ciudades=$model->getCiudades();
        $piloto=AppModelUsuario::getInstance()->getPerfil($viaje["usuario_id"])[0];
        $cantidadAceptados=$dbViaje->contarAceptados($datos);
        $cantidadAceptados=$cantidadAceptados[0]["COUNT(*)"];
        $preguntasYrespuestas = AppModelUsuario::getInstance()->preguntasYRespuestas($datos["id"]);
        if(isset($_SESSION["id"])){
            $postulado=$dbViaje->yaMePostule($datos);
            $postulados=$dbViaje->getPostulados($datos);
            $view->verPublicacionViaje($viaje,$calificaciones,$vehiculo,$ciudades, $piloto, $postulado, $postulados, $cantidadAceptados, $datos, $preguntasYrespuestas);
        } else {
            $postulados=$dbViaje->getPostulados($datos);
            $view->verPublicacionViaje($viaje,$calificaciones,$vehiculo,$ciudades, $piloto, '', $postulados, $cantidadAceptados, $datos, $preguntasYrespuestas);
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

        /* 

        ACA SE TIENE QUE DESCONTAR PUNTOS!!! 

        */
        $this->ver_publicacion_viaje($datos);
    }

    public function modificarViajeOcasional($datos){
        if($this->validarViajeOcasionalModificado($datos)){
            $bd = AppModelViaje::getInstance();
            $asientos = AppModel::getInstance()->getAsientos($datos["vehiculo"]);
            $datos["asientos"] = $asientos[0]["asientos"];
            //var_dump($datos);
            $bd->actualizarViajeOcasional($datos);
        }
        $this->ver_publicacion_viaje(["id"=>$datos["id"]]);
    }

    public function validarViajeOcasionalModificado($datos){
        
        $tempArray = explode('-',$datos["fecha"]);
        for ($i=0;$i<count($tempArray);$i++){
            $tempArray[$i] = (int)$tempArray[$i];
        }
        $entra = true;
        $errno=array();
        if(!$this->fechaMayor($tempArray)){
            //echo "Fecha ingresada invalida";
            $errno["fechaMayor"]="Fecha ingresada invalida";
            $entra = false;
        }
        if(!$this->esNumerico($datos["precio"])){
            //echo "El precio no puede tener letras";
            $errno["precio"]="El precio no puede tener letras";
            $entra = false;
        }
        if(!$this->esNumerico($datos["duracion"])){
            //echo "La duracion no puede tener letras";
            $errno["duracion"]="La duracion no puede tener letras";
            $entra = false;
        }
        if($datos["origen"]==$datos["destino"]){
            //echo "El origen y el destino no pueden ser los mismos";
            $errno["origen"]="El origen y el destino no pueden ser los mismos";
            $entra = false;
        }
        if(!$this->esNumerico($datos["distancia"])){
            //echo "La distancia no puede tener letras";
            $errno["distancia"]="La distancia no puede tener letras";
            $entra = false;
        }
        if($this->esHoy($tempArray)){
            if($this->masTarde($datos["hora_salida"])){
                //echo "Debe ser para mas tarde";
                $errno["hora"]="Debe ser para mas tarde";
                $entra = false;
            }
        }
        if($entra){
            if(!AppControllerVehiculo::getInstance()->vehiculoViajaModificado($datos)){
                //echo "El vehiculo tiene un viaje para ese horario";
                $errno["viajes"]="El vehiculo tiene un viaje para ese horario";
                $entra = false;
            }
        }
        $_SESSION["errno"]=$errno;
        //$_SESSION["errno"]["g"]=0;
        return $entra;
    }
    

    public function cancelar_postulacion_aceptada($datos){
        $view = new Home();
        $view->cancelarPostulacionAceptada($datos);
    }

    public function borrar_postulacion_aceptada($datos){
        AppModelViaje::getInstance()->cancelarPostulacion($datos);
        // ACA HAY QUE DESCONTAR LOS PUNTOS PERO NO ESTA LA PUNTUACION
        $this->ver_publicacion_viaje($datos);
    }

    public function aceptarPostulacionAViaje($datos){   
        $bd= AppModelViaje::getInstance();
        $postulado=$datos["id_usuario"];
        $viaje=$datos["id"];
        if($this->viajeTieneLugar($viaje)){
            $bd->cambiarEstadoParaAceptado($viaje, $postulado);
            $errno["aceptado"]="Se acepto al usuario correctamente";
            $_SESSION["errno"]=$errno;
            //Echo "Se acepto al usuario correctamente";
        } else {
            $errno["rechazado"]="Ya no puedes aceptar mas usuarios!";
            $_SESSION["errno"]=$errno;
            //Echo "Ya no puedes aceptar mas usuarios!";
        }
        $this->ver_publicacion_viaje($datos);
    }

    public function viajeTieneLugar($idViaje){
        $lugar = true;
        $bd = AppModelViaje::getInstance();
        $vector["id"]=$idViaje;
        $viaje = $bd->getViaje($vector);
        $cantidadAceptados = $bd->contarAceptados($vector);
        if($cantidadAceptados[0][0] >= $viaje["lugares"]-1){
            $lugar = false;
        }
        return $lugar;
    }

    public function confirmarEliminacionViaje($datos){
        $view = new Home();
        $bdViaje = AppModelViaje::getInstance();
        $aceptados=$bdViaje->aceptadosParaEsteViaje($datos["id"]);
        $cantidadAceptados = count($aceptados);
        if($cantidadAceptados!=0){
            $calificacion["usuario_id"]=$_SESSION["id"];
            $calificacion["puntaje"]=-1;
            AppModelUsuario::getInstance()->actualizarPuntajePiloto($calificacion);
            //pierde 1 punto, llamar a funcion de calificaciones
        }
        $view->eliminarViaje($datos, $cantidadAceptados);
    }

    public function rechazarPostulacion($datos){
        $view = new Home();
        $bdViaje = AppModelViaje::getInstance();
        $postulado=$datos["id_usuario"];
        $viaje=$datos["id"];
        $bdViaje->cambiarEstadoARechazado($viaje, $postulado);
        $datos["usuario_id"]=$_SESSION["id"];
        AppModelUsuario::getInstance()->actualizarPuntajePiloto($datos);
        $this->ver_publicacion_viaje($datos);
    }
}