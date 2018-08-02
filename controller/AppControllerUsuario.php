<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');

require_once('controller/AppController.php');


class AppControllerUsuario {
    
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
    
    public function registrarse(){
        $view = new Home();
        $view->show("registrarse.html.twig");
    }

    public function crear_usuario($datos){
        //agrega el usuario en la bd
        $bdUsuario = AppModelUsuario::getInstance();
        $view = new Home();
        if(isset($datos)){
            $test = $this->validacionUsuario($datos);
            if(!($bdUsuario->existeMail($datos["email"]))&&($test)){
                $bdUsuario->registrar($datos);
                $errno["crear_usuario"]="Se registro con exito!";
                $_SESSION["errno"]["bueno"]=$errno;
                $menu = AppController::getInstance();
                $menu->index();
            } else {
                if ($bdUsuario->existeMail($datos["email"])){
                    //echo "Ya existe el mail en la base de datos";
                    $errno["crear_usuario"]="Ese mail ya se encuentra registrado";
                    $_SESSION["errno"]["malo"]=$errno;
                }
                $view->show("registrarse.html.twig");
            }
        }           
    }

    public function containsNumbers($String){
        //devuelve true si el string recibido tiene numeros, y false si no tiene
        return preg_match('/\\d/', $String) > 0;
    }
    
    public function mayorDeEdad($String){
        //devuelve true si la fecha recibida tiene mas de 15 años, caso contrario devuelve false
        $tempArray = explode('-',$String);
        $anio = (int) date('y')+2000;
        $mes = (int) date('m');
        $dia = (int) date('d');
        $bool = false;
        if($anio-$tempArray[0]>15){
            if($anio-$tempArray[0]==16){
                if ($mes-$tempArray[1]>-1){
                    if($dia-$tempArray[2]>-1){
                        $bool = true;
                    }
                }
            } else {
                $bool = true;
            }           
        }
        return $bool;
    }
    
    public function validacionUsuario($datos){
        //valida los datos desde servidor
        $valor=true;  
        $errno = array();  
        if(!(preg_match("#^([^0-9]*)$#",$datos["nombre"]))){
            //echo "El nombre no puede tener numeros";
            $errno["nombre"]="El nombre no puede tener numeros";
            $valor= false;
        }
        if(!(preg_match("#^([^0-9]*)$#",$datos["apellido"]))){
            //echo "El apellido no puede tener numeros";
            $errno["apellido"]="El apellido no puede tener numeros";
            $valor= false;
        }
        if(!($datos["pass"]==$datos["pass1"])){
            //echo "Las contraseñas no coinciden ";
            $errno["pass"]="Las contraseñas no coinciden";
            $valor= false;
        }
        if(!(strlen($datos["pass"])>7)){
            //echo "La contraseña es muy corta ";
            $errno["longitud"]="La contraseña es muy corta";
            $valor= false;
        }
        if(!((preg_match("#\W+#", $datos["pass"]))or($this->containsNumbers($datos["pass"])))){
            //echo "La contraseña no contiene un simbolo o un numero ";
            $errno["char"]="La contraseña no contiene un simbolo o un numero";
            $valor= false;          
        }
        if(!($this->mayorDeEdad($datos["nacimiento"]))){
            //echo "Necesitas tener al menos 16 años para registrarte al sitio ";
            $errno["edad"]="Necesitas tener al menos 16 años para registrarte al sitio";
            $valor= false;
        }
        $_SESSION["errno"]["malo"]=$errno;
        return $valor;
    }

    public function mostrarPerfil($guia){
        //busca los datos a mostrar para el perfil del usuario
        $bdUsuario = AppModelUsuario::getInstance();
        if(isset($_SESSION)){
            $datosUsuario = $bdUsuario->getPerfil($_SESSION['id']);
            $nombre = $datosUsuario[0]["nombre"]." ".$datosUsuario[0]["apellido"];
            $mostrarDatos["nombre"] = $nombre;
            $mostrarDatos["email"] = $datosUsuario[0]["email"];
            $mostrarDatos["usuario_id"] = $_SESSION['id'];
            $view = new Home();
            //$viajes = $bdUsuario->getViajesPropios($_SESSION['id']);
            if ($guia == "futuro"){
                $mostrarDatos["viajes"] = $bdUsuario->getViajesPropios($_SESSION['id']);
                $mostrarDatos["postulaciones"] = $bdUsuario->getMisPostulaciones($_SESSION['id']);
                $mostrarDatos["tituloDinamico"] = "Mis proximos viajes como piloto";
                $mostrarDatos["tituloDinamico2"] = "Mis solicitudes como copiloto";
            } elseif ($guia == "totales"){
                $mostrarDatos["viajes"] = $bdUsuario->getViajesPiloto($_SESSION['id']);
                $mostrarDatos["postulaciones"] = $bdUsuario->getViajesCopiloto($_SESSION['id']);
                $mostrarDatos["tituloDinamico"] = "Mis viajes hechos como piloto";
                $mostrarDatos["tituloDinamico2"] = "Mis viajes hechos como copiloto";
            }
            $ciudades = AppModel::getInstance()->getCiudades();
            $mostrarDatos["ciudades"]=$ciudades;
            $mostrarDatos["calificacion_piloto"] = $bdUsuario->calificacionPiloto($_SESSION['id']);
            $mostrarDatos["cantidadViajesPiloto"] = $bdUsuario->viajesHechosComoPiloto($_SESSION['id']);
            $mostrarDatos["calificacion_copiloto"] = $bdUsuario->calificacionCopiloto($_SESSION['id']);
            $mostrarDatos["cantidadViajesCopiloto"] = $bdUsuario->viajesHechosComoCopiloto($_SESSION['id']);
            $mostrarDatos["calificacionesPendientesAPilotos"] = $bdUsuario->pilotosACalificar($_SESSION['id']);
            $mostrarDatos["calificacionesPendientesACopilotos"] = $bdUsuario->copilotosACalificar($_SESSION['id']);
            $view->mostrarNombre($mostrarDatos); //falta
        }

        //Utilice el else if para mostrar el listado de viajes tanto de piloto como de copiloto
        //muestra TODOS los viajes que realicé, tanto de piloto como copiloto
    }

    public function modificar_perfil(){
        $view = new Home();
        //busca los datos anteriores del perfil
        $datosUsuario = AppModelUsuario::getInstance()->getPerfil($_SESSION['id']);
        $datosUsuario[0]["visibilidad"]=0;
        $view->camposModificarPerfil($datosUsuario[0]); //falta     
    }

    public function actualizar_perfil($datos){
        $bd = AppModelUsuario::getInstance();
        $datosUsuario = AppModelUsuario::getInstance()->getPerfil($_SESSION["id"]);
        $view = new Home();
        if(isset($datos)){
            if(!(($datos["oldPass"])==""||($datos["oldPass"])==null)&&($datos["oldPass"]==$datosUsuario[0]["password"])){
                if($this->validacionActualizarUsuario($datos)){
                    $datos["id"] = $_SESSION['id'];
                    $bd->actualizarUsuario($datos);
                    //echo "Datos actualizados!";
                    $errno["modificar_perfil"]="Datos actualizados correctamente!";
                    $_SESSION["errno"]["bueno"]=$errno;
                    $this->mostrarPerfil("futuro");
                } else {
                    ////REVISAR QUE ERROR TIENE QUE DAR
                    //errno se esta cargando en validacion!
                    $datosUsuario[0]["visibilidad"]=0;
                    $view->camposModificarPerfil($datosUsuario[0]); //falta
                }
            } else {
                //echo "Contraseña incorrecta";
                $datosUsuario[0]["visibilidad"]=0;
                $errno["contraseña_incorrecta"]="Contraseña incorrecta";
                $_SESSION["errno"]=$errno;
                $view->camposModificarPerfil($datosUsuario[0]); //falta
            }
        } else {
            $this->mostrarPerfil("futuro");
        }
    }

    public function validacionActualizarUsuario($datos){
        //valida los datos desde servidor

        $valor=true;  
        $errno = array();  
        if(!(preg_match("#^([^0-9]*)$#",$datos["nombre"]))){
            //echo "El nombre no puede tener numeros";
            $errno["nombre"]="El nombre no puede tener numeros";
            $valor= false;
        }
        if(!(preg_match("#^([^0-9]*)$#",$datos["apellido"]))){
            //echo "El apellido no puede tener numeros";
            $errno["apellido"]="El apellido no puede tener numeros";
            $valor= false;
        }
        if(!($this->mayorDeEdad($datos["nacimiento"]))){
            //echo "Necesitas tener al menos 16 años para registrarte al sitio ";
            $errno["edad"]="Necesitas tener al menos 16 años para registrarte al sitio";
            $valor= false;
        }
        $_SESSION["errno"]["malo"]=$errno;
        return $valor;
    }

    public function actualizar_password($datos){
        $bd = AppModelUsuario::getInstance();
        $datosUsuario = AppModelUsuario::getInstance()->getPerfil($_SESSION["id"]);
        $view = new Home();
        if(isset($datos)){
            if(!(($datos["oldPass"])==""||($datos["oldPass"])==null)&&($datos["oldPass"]==$datosUsuario[0]["password"])){
                if($this->validacionActualizarPassword($datos)){
                    $datos["id"] = $_SESSION['id'];
                    $bd->actualizarPassword($datos);
                    //echo ("Contraseña actualizada!");
                    $errno["contraseña_actualizada"]="Contraseña actualizada";
                    $_SESSION["errno"]["bueno"]=$errno;
                    $this->mostrarPerfil("futuro");
                } else {
                    $datosUsuario[0]["visibilidad"]=1;
                    $view->camposModificarPerfil($datosUsuario[0]); //falta
                }
            } else {
                $datosUsuario[0]["visibilidad"]=1;
                //echo "Contraseña incorrecta";
                $errno["contraseña_incorrecta"]="Contraseña incorrecta";
                $_SESSION["errno"]["malo"]=$errno;
                $view->camposModificarPerfil($datosUsuario[0]); //falta
            }
        }
    }

    public function validacionActualizarPassword($datos){
        $valor=true;  
        $errno = array();  
        if(!($datos["pass"]==$datos["pass1"])){
            //echo "Las contraseñas no coinciden ";
            $errno["pass"]="Las contraseñas no coinciden";
            $valor= false;
        }
        if(!(strlen($datos["pass"])>7)){
            //echo "La contraseña es muy corta ";
            $errno["longitud"]="La contraseña es muy corta";
            $valor= false;
        }
        if(!((preg_match("#\W+#", $datos["pass"]))or($this->containsNumbers($datos["pass"])))){
            //echo "La contraseña no contiene un simbolo o un numero ";
            $errno["char"]="La contraseña no contiene un simbolo o un numero";
            $valor= false;          
        }
        $_SESSION["errno"]["malo"]=$errno;
        return $valor;
    }

    public function publicar_pregunta($datos){
        AppModelUsuario::getInstance()->publicarPregunta($datos);
        AppControllerViajes::getInstance()->ver_publicacion_viaje($datos);
    }

    public function responder_pregunta($datos){
        AppModelUsuario::getInstance()->publicarRespuesta($datos);
        AppControllerViajes::getInstance()->ver_publicacion_viaje($datos);
    }

    public function calificarPiloto($datos){
        $view = new Home();
        $datosCompletos = AppModelUsuario::getInstance()->getDatosCalifcacionPiloto($datos);
        $ciudades=AppModel::getInstance()->getCiudades();
        $rol = "Piloto";
        $view->calificacion($datosCompletos,$ciudades,$rol);
    }

    public function calificar_piloto($datos){
        $bdUsuario = AppModelUsuario::getInstance();
        $bdUsuario->calificarPiloto($datos);
        $bdUsuario->actualizarPuntajePiloto($datos);
        //echo("El piloto fue calificado con exito");
        $errno["calificar_piloto"]="El piloto fue calificado con exito";
        $_SESSION["errno"]["bueno"]=$errno;
        $this->mostrarPerfil("futuro");
    }

    public function calificarCopiloto($datos){
        $view = new Home();
        $datosCompletos = AppModelUsuario::getInstance()->getDatosCalifcacionCopiloto($datos);
        $ciudades=AppModel::getInstance()->getCiudades();
        $rol = "Copiloto";
        $view->calificacion($datosCompletos,$ciudades,$rol);
    }

    public function calificar_copiloto($datos){
        $bdUsuario = AppModelUsuario::getInstance();
        $bdUsuario->calificarCopiloto($datos);
        $bdUsuario->actualizarPuntajeCopiloto($datos);
        //echo("El copiloto fue calificado con exito");
        $errno["calificar_copiloto"]="El copiloto fue calificado con exito";
        $_SESSION["errno"]["bueno"]=$errno;
        $this->mostrarPerfil("futuro");
    }

    public function listarViajesAPagar(){
        $viajes=AppModelUsuario::getInstance()->listaViajesAPagar();
        $ciudades=AppModel::getInstance()->getCiudades();
        $view = new Home();
        $view->listarViajesAPagar($viajes,$ciudades);
    }

    public function pagarViaje($datos){
        $model=AppModelViaje::getInstance();
        $viaje=$model->getViaje($datos);
        $viaje["origen"]=$model->getCiudadForId($viaje["origen_id"])[0][0];
        $viaje["destino"]=$model->getCiudadForId($viaje["destino_id"])[0][0];
        $viaje["cant_copilotos"]=$model->contarAceptados($datos)[0][0];
        $vehiculo=AppModel::getInstance()->getVehiculo($viaje["vehiculo_id"])[0];
        $view = new Home();
        $view->pantallaParaPagar($viaje,$vehiculo);
    }

    public function validarPago($datos){
        $valida=$this->validarTarjetaDeCredito($datos);
        if($valida){
            $this->realizarPago($datos);
            $cant_viajes_a_pagar= AppModelUsuario::getInstance()->tengoViajesAPagar();
            if($cant_viajes_a_pagar[0]["COUNT(v.id)"]>0){
                $this->listarViajesAPagar();
            } else {
                AppController::getInstance()->mostrarMenuConSesion();
            }
        } else {
            //echo('El pago no pudo realizarse, los datos ingresados no coinciden!');
            $errno["validarPago"]="El pago no pudo realizarse, los datos ingresados no coinciden! Vuelva a intentarlo";
            $_SESSION["errno"]["malo"]=$errno;
            $this->pagarViaje($datos);
            //informar error en el pago
        }
    }

    public function validarTarjetaDeCredito($datos){
        $valida=AppModelUsuario::getInstance()->validadorDeTarjetas($datos);
        //revisar el saldo en la tarjeta!
        return $valida;
    }

    public function realizarPago($datos){
        //consulta para cambiar estado!
        AppModelUsuario::getInstance()->setearPagado($datos["id"]);
        //echo('El pago se realizo correctamente!');
        $errno["realizarPago"]="El pago se realizo correctamente!";
        $_SESSION["errno"]["bueno"]=$errno;
    }

    public function verPerfilAjeno($get){
        $bdUsuario = AppModelUsuario::getInstance();
        if (($get["email"]) == "yaTengoElID"){ //con este if diferencio si ya tengo el id desde twig.
            $idUsr[0][0]= ($get["id"]);
        }else{                           // Si vine por un piloto lo hago con el mail, asi que obtengo su ID.
            $idUsr = $bdUsuario->getIdAjeno($get["email"]);
        }
        if ($idUsr[0][0] == ($_SESSION['id'])){  //con este if estoy validando si soy yo mismo.
            $this->mostrarPerfil("futuro");
        } else {
            if(isset($_SESSION)){
                $datosUsuario = $bdUsuario->getPerfil($idUsr[0][0]);
                $nombre = $datosUsuario[0]["nombre"]." ".$datosUsuario[0]["apellido"];
                $mostrarDatos["nombre"] = $nombre;
                $mostrarDatos["email"] = $datosUsuario[0]["email"];
                $mostrarDatos["usuario_id"] = $idUsr[0][0];
                $view = new Home();
                $viajes = $bdUsuario->getViajesPropios($idUsr[0][0]);
                $mostrarDatos["calificacion_piloto"] = $bdUsuario->calificacionPiloto($idUsr[0][0]);
                $mostrarDatos["cantidadViajesPiloto"] = $bdUsuario->viajesHechosComoPiloto($idUsr[0][0]);
                $mostrarDatos["calificacion_copiloto"] = $bdUsuario->calificacionCopiloto($idUsr[0][0]);
                $mostrarDatos["cantidadViajesCopiloto"] = $bdUsuario->viajesHechosComoCopiloto($idUsr[0][0]);
                $view->mostrarNombreAjeno($mostrarDatos); 
            }
        }
    }

    public function tieneCalificacionesPendientes(){
        $bdUsuario = AppModelUsuario::getInstance();
        $cantCalificacionesAPilotos = $bdUsuario->pilotosACalificarMayoresA30($_SESSION['id']);
        $cantCalificacionesACopilotos = $bdUsuario->copilotosACalificarMayoresA30($_SESSION['id']);
        $cantCalificaciones = count($cantCalificacionesAPilotos) + count($cantCalificacionesACopilotos);
        var_dump($cantCalificacionesAPilotos);
        var_dump($cantCalificacionesACopilotos);
        var_dump($cantCalificaciones);
        if($cantCalificaciones == 0){
            return false;
        } else {
            return true;
        }
    }

    public function mostrarCalificacionesDetalladas($datos){
        $bdUsuario = AppModelUsuario::getInstance();
        $bdViaje = AppModelViaje::getInstance();
        $mostrarDatos["calificacionPiloto"] = $bdUsuario->getCalificacionesPiloto($datos["usuario_id"]);
        $mostrarDatos["calificacionCopiloto"] = $bdUsuario->getCalificacionesCopiloto($datos["usuario_id"]);
        $view = new Home();
        $view->calificacionesDetalladas($mostrarDatos);
    }
}