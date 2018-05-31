<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');


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
			$view->listarViajesGenerales("index.html.twig", $viajes);
		} else {
			$view = new Home();
			$this->mostrarMenuPrincipalSesion();
		}
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
		$bd = AppModel::getInstance();
		$view = new Home();
		if(isset($datos)){
			$test = $this->validacionUsuario($datos);
			if(!($bd->existeMail($datos["email"]))&&($test)){
				$bd->registrar($datos);
				$view->show("index.html.twig");
			} else {
				if ($bd->existeMail($datos["email"])){
					echo "Ya existe el mail en la base de datos ";
				}
				$view->show("registrarse.html.twig");
			}
		}			
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
		$view->formularioTipoVehiculos($vector,$string);
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
		if(!(preg_match("#^([^0-9]*)$#",$datos["nombre"]))){
			echo "El nombre no puede tener numeros";
			$valor= false;
		}
		if(!(preg_match("#^([^0-9]*)$#",$datos["apellido"]))){
			echo "El apellido no puede tener numeros";
			$valor= false;
		}
		if(!($datos["pass"]==$datos["pass1"])){
			echo "Las contraseñas no coinciden ";
			$valor= false;
		}
		if(!(strlen($datos["pass"])>7)){
			echo "La contraseña es muy corta ";
			$valor= false;
		}
		if(!((preg_match("#\W+#", $datos["pass"]))or($this->containsNumbers($datos["pass"])))){
			echo "La contraseña no contiene un simbolo o un numero ";
			$valor= false;			
		}
		if(!($this->mayorDeEdad($datos["nacimiento"]))){
			echo "Necesitas tener al menos 16 años para registrarte al sitio ";
			$valor= false;
		}
		return $valor;
	}

	public function validar_Inicio_Sesion(){
		if(isset($_POST['usr'])&&isset($_POST['contraseña'])){   
           	$datos['nombre_usuario'] = $_POST['usr'];
            $datos['contraseña_usuario'] = $_POST['contraseña'];
            $usuario = AppModel::getInstance()->existeUsuario($datos['nombre_usuario'],$datos['contraseña_usuario']);
                if (count($usuario) == 0){
                    //Aviso que no existe usuario o que no corresponde con su psw
                    $view = new Home();
        			$view->errorLogin("el usuario no corresponde con la contraseña");
                }else{
                	$vector_usuario = AppModel::getInstance()->getId($datos['nombre_usuario']);
					$usuario_id = (int)$vector_usuario[0][0];
					$_SESSION["id"]= $usuario_id;
					$view = new Home();
            		$this->mostrarMenuPrincipalSesion();
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

	public function mostrarPerfil(){
		//busca los datos a mostrar para el perfil del usuario
		if(isset($_SESSION)){
			$datosUsuario = AppModel::getInstance()->getPerfil($_SESSION['id']);
			$nombre = $datosUsuario[0]["nombre"]." ".$datosUsuario[0]["apellido"];
			$mostrarDatos["nombre"] = $nombre;
			$mostrarDatos["email"] = $datosUsuario[0]["email"];
			$view = new Home();
			$viajes = AppModel::getInstance()->getViajesPropios($_SESSION['id']);
			$mostrarDatos["viajes"]=$viajes;
			/*var_dump($viajes);*/
			$view->mostrarNombre($mostrarDatos);
		}
	}

	public function crear_vehiculo($datos){
		$bd = AppModel::getInstance();
		$view = new Home();
		if(isset($datos)){
			$test = $this->validar_vehiculo($datos);
			if(($bd->existeTipo($datos["tipo"]))&&($test)&&preg_match("#[1-9][0-9]?#",$datos["asientos"])){
				$datos["id_usuario"] = $_SESSION['id'];
				$bd->registrar_vehiculo($datos);
				$this->mostrarMenuPrincipalSesion();
			} else {
				$this->registrar_vehiculo();
			}
		}
	}

	public function validar_vehiculo($datos){
		$valor = ((preg_match("#[A-Za-z]{2}[0-9]{3}[A-Za-z]{2}|[A-Za-z]{3}[0-9]{3}#", $datos["patente"])) && (preg_match("#[1-2][0-9]{3}#", $datos["modelo"])));
		return $valor;
	}
	
	public function modificar_perfil(){
		$view = new Home();
		//busca los datos anteriores del perfil
        $datosUsuario = AppModel::getInstance()->getPerfil($_SESSION['id']);
		$view->camposModificarPerfil($datosUsuario[0]);		
	}

	public function actualizar_perfil($datos){
		$bd = AppModel::getInstance();
		$datosUsuario = AppModel::getInstance()->getPerfil($_SESSION["id"]);
		$view = new Home();
		if(isset($datos)){
			if(!(($datos["oldPass"])==""||($datos["oldPass"])==null)&&($datos["oldPass"]==$datosUsuario[0]["password"])){
				if($this->validacionUsuario($datos)){
					$datos["id"] = $_SESSION['id'];
					$bd->actualizarUsuario($datos);
					$this->mostrarPerfil();
				} else {
					$view->camposModificarPerfil($datosUsuario[0]);
				}
			} else {
				echo "Contraseña incorrecta";
				$view->camposModificarPerfil($datosUsuario[0]);
			}
		}
	}
		
		/*if(isset($datos)){
			$test = $this->validacionModificacionUsuario($datos);
			if($test){
				$bd->actualizar_usuario($datos);
				$view->show("index.html.twig");
			} else {
				if ($bd->existeMail($datos["email"])){
					echo "Ya existe el mail en la base de datos ";
				}
				$view->show("registrarse.html.twig");
			}
		} else {
		$view->show("registrarse.html.twig");
		}			
	}

	public function validacionModificacionUsuario($datos){
		//valida los datos desde servidor
		$valor = true;
		var_dump($datos["pass"]);
		if(isset($datos["pass"]) && $datos["pass"]!=""){
			if(isset($datos["pass1"]) &&!($datos["pass"]==$datos["pass1"])){
				echo "Las contraseñas no coinciden ";
				$valor = false;
			}
			if(!(strlen($datos["pass"])>7)){
				echo "La contraseña es muy corta ";
				$valor = false;
			}
			if(!((preg_match("#\W+#", $datos["pass"]))or($this->containsNumbers($datos["pass"])))){
				echo "La contraseña no contiene un simbolo o un numero ";
				$valor = false;			
			}

		}
		if(!($this->mayorDeEdad($datos["nacimiento"]))){
			echo "Necesitas tener al menos 15 años para registrarte al sitio ";
			$valor = false;
		}

		if(!((preg_match("#\W+#", $datos["oldPass"]))or($this->containsNumbers($datos["oldPass"])))){
			echo "La contraseña no contiene un simbolo o un numero ";
			$valor = false;			
		}
		return $valor;
	}*/
/*
	public function actualizar_perfil($datos){
		$bd = AppModel::getInstance();
		$view = new Home();
		if(isset($datos)){
			$test = $this->validacionModificacionUsuario($datos);
			if($test){
				$bd->actualizar_usuario($datos);
				$view->show("index.html.twig");
			} else {
				if ($bd->existeMail($datos["email"])){
					echo "Ya existe el mail en la base de datos ";
				}
				$view->show("registrarse.html.twig");
			}
		} else {
		$view->show("registrarse.html.twig");
		}			
	}
*/
	public function validacionModificacionUsuario($datos){
		//valida los datos desde servidor
		$valor = true;
		if(isset($datos["pass"]) && $datos["pass"]!=""){
			if(isset($datos["pass1"]) &&!($datos["pass"]==$datos["pass1"])){
				echo "Las contraseñas no coinciden ";
				$valor = false;
			}
			if(!(strlen($datos["pass"])>7)){
				echo "La contraseña es muy corta ";
				$valor = false;
			}
			if(!((preg_match("#\W+#", $datos["pass"]))or($this->containsNumbers($datos["pass"])))){
				echo "La contraseña no contiene un simbolo o un numero ";
				$valor = false;			
			}

		}
		if(!($this->mayorDeEdad($datos["nacimiento"]))){
			echo "Necesitas tener al menos 15 años para registrarte al sitio ";
			$valor = false;
		}
		if(!((preg_match("#\W+#", $datos["oldPass"]))or($this->containsNumbers($datos["oldPass"])))){
			echo "La contraseña no contiene un simbolo o un numero ";
			$valor = false;			
		}
		return $valor;
	}
	
	public function buscador($datos){
		$view = new Home();
		if(isset($datos["origen"]) && isset($datos["salida"]) && (($datos["origen"]!="")&& $datos["salida"]!="")){
			if(isset($datos["destino"]) && $datos["destino"]!=""){
				$viajes= AppModel::getInstance()->busqueda_completa($datos);
				$view->listarViajes($viajes);
			} else {
				$viajes= AppModel::getInstance()->busqueda_parcial($datos);
				$view->listarViajes($viajes);
			}
		} else {
			echo "Faltan ingresar datos";
		}
	}
	
	public function listar_vehiculos(){
		$view = new Home();
		$vehiculos=AppModel::getInstance()->getVehiculos(); 
		$view->listarVehiculosPropios($vehiculos);
	}

/*
	public function poseeViajes($datos){
		return AppModel::getInstance()->poseeViajesEchos($datos);
	}
*/
	public function eliminar_vehiculo($datos){
		$viajes=AppModel::getInstance()->poseeViajesEchos($datos);
		if($viajes){
			/* borrar de usuarios_has_vehiculo */
			AppModel::getInstance()->eliminarRelacionUsuarioVehiculo($datos);
		} else {
			/* borrar de usuarios_has_vehiculos y de vehiculos */
			AppModel::getInstance()->eliminarRelacionUsuarioVehiculo($datos);
			AppModel::getInstance()->borrarVehiculo($datos);
		}
		$view = new Home();
		$this->listar_vehiculos();
	}

	public function eliminarViaje($idViaje){
		$today = date("Y/m/d");
		$fechaViaje = ($_POST['fecha']);
		if ( $today > $fechaViaje ){ 
			// if ($this->sin_acompaniantes($idViaje)){} verifica si hay gente ya aceptada en el viaje
			$this->eliminarViajeDeLaBD($idViaje);
			// $this->listar_usuarios(); lista con el viaje ya eliminado (funcion sin hacer)
			Echo "el viaje se eliminó con exito";
		} else { 
			echo "el viaje ya se realizó";
		}
		$this->mostrarPerfil();
	}

	public function eliminarViajeDeLaBD($idViaje){	
		AppModel::getInstance()->eliminarViajeOcasional($idViaje);
		AppModel::getInstance()->eliminarViajePeriodicoDias($idViaje);
		AppModel::getInstance()->eliminarViajePeriodico($idViaje);
		AppModel::getInstance()->eliminarViaje($idViaje);
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
		$view->modificarVehiculo($string,$vehiculo);
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
				$this->mostrarMenuPrincipalSesion();
			} else {
				$vehiculos=$bd->getVehiculos(); 
				$view->$view->listarVehiculosPropios($vehiculos);
			}
		}
	}
	
	public function mostrarMenuPrincipalSesion(){
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
        //Muestra la pagina con el listado de viajes
        $parametros = $this->listadoViajesGenerales();
       // $view = new Home();
        $arreglo = array();
        $arreglo['mensajeDeResultado'] = $parametros['mensaje'];
        if(isset($parametros['listaViajes'])){
            $arreglo['listadoCompletoDeViajes'] = $parametros['listaViajes'];
            $arreglo['elemPorPagina'] = 3;
        }
       // $view->listarViajesGenerales('index.html.twig',$arreglo);
        return $arreglo;
    }

    public function listadoViajesGenerales(){

        /*
        *CONTROLAR QUE EL VIAJE SEA EN LOS PROXIMOS 30 DIAS!
        */

        $viajesVar = AppModel::getInstance()->getViajes();
        $parametros = array();
        if(count($viajesVar) == 0){
        	$parametros['mensaje'] = 'No hay viajes registrados.';
        }else{
        	$parametros['listaViajes'] = $viajesVar;
	        $parametros['mensaje'] = 'Listado de viajes.';
        }
        return $parametros;
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
	
	public function publicarViajeOcasional($datos){
		if($this->validarViajeOcasional($datos)){
			$bd = AppModel::getInstance();
			$asientos = $bd->getAsientos($datos["vehiculo"]);
			$datos["asientos"] = $asientos[0]["asientos"];
			$idViaje = $bd->getViajeId($datos);
			$datos["id_viaje"] = $idViaje;
			$bd->crearOcasional($datos);
			$this->mostrarMenuPrincipalSesion();
		} else {
			$this->mostrarMenuPrincipalSesion();
		}		
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

	public function modificar_viaje_ocasional($datos){

		$view = new Home();
		$bd = AppModel::getInstance();
		$ciudades = $bd->getCiudades();
		$vectorFormulario["ciudades"] = $ciudades;
		$vehiculosUsuario = $bd->getVehiculos();
		$vectorFormulario["vehiculos"] = $vehiculosUsuario;

		$viaje = $bd->getViajeOcasional($datos);		
		$view->modificarViajeOcasional($viaje, $vectorFormulario);


	}

	public function modificarViajeOcasional($datos){
		$view = new Home();
		/*$viaje= $bd->getViajeOcasional($datos["id"]);*/
		$valido=$this->validarViajeOcasional($datos);

		if($valido){
			$db = AppModel::getInstance();
			$db-> actualizarViajeOcasional($datos);
		}
		$this->mostrarMenuPrincipalSesion();	
	}
	
	/*public function fechaViajesPeriodicos($fechaInicial){
		// Create a new DateTime object
		$date = new DateTime($fechaInicial);
		// Modify the date it contains
		$date->modify('next Wednesday');
	
		// Output
		echo $date->format('Y-m-d');
		var_dump( date('w', strtotime($fechaInicial)));
	}*/
	
	public function publicarViajePeriodico($datos){
		$test = $this->validarViajePeriodico($datos);
		if($test){	
			$bd = AppModel::getInstance();
			$asientos = $bd->getAsientos($datos["vehiculo"]);
			$datos["asientos"] = $asientos[0]["asientos"];
			$fechas = $this->diasViajePeriodico($datos);			
			$vectorFechas = $this->acomodarVectorFechas($fechas,$datos);
			foreach(array_keys($vectorFechas) as $fecha){
				$datos["fecha"] = $fecha;
				$datosPeriodico["viajeId"] = $bd->getViajeId($datos);
				$datosPeriodico["fechaFinal"] = $fecha;
				$datosDiaHorario["horario"] = $vectorFechas[$fecha];
				$datosDiaHorario["fecha"] = $datosPeriodico["fechaFinal"];
				$datosDiaHorario["idViaje"] = $datosPeriodico["viajeId"];
				$bd->asociarPeriodico($datosPeriodico);
				$bd->asociarDiaHorario($datosDiaHorario);
			}
			$this->mostrarMenuPrincipalSesion();
		} else {
			$this->mostrarMenuPrincipalSesion();
		}		
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
		/*for ($i=0;$i<count($vector);$i++){
			$vector[$i] = $vector[$i]->format('Y-m-d');
		}*/
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
}

