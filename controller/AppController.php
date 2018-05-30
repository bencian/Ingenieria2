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
			$view->show("index.html.twig");
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
			$view = new Home();
			$view->show("index.html.twig");
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
		$this->mostrarMenuPrincipalSesion();
	}

	public function eliminarViaje($idViaje){
		$today = date("Y/m/d");
		$fechaViaje = ($_POST['fecha']);
		if ( $today < $fechaViaje ){ 
			// if ($this->sin_acompaniantes($idViaje)){} verifica si hay gente ya aceptada en el viaje
			$result = AppModel::getInstance()->eliminarViaje($idViaje);
			// $this->listar_usuarios(); lista con el viaje ya eliminado (funcion sin hacer)
			Echo "el viaje se eliminó con exito";
		} else { 
			echo "el viaje ya se realizó";
		}
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
		$view->listarCiudadesMenuPrincipal($vectorFormulario);
	}

	public function listadoViajesGenerales(){
        //Lista todos los viajes con algunos detalles
        $viajes = AppModel::getInstance()->getViajes();
        if(count($viajes) == 0){
        	$parametros['mensaje'] = 'No hay viajes registrados.';
    	    $this->accesoAPaginaQueLista($parametros);
        }else{
        	$parametros['listaViajes'] = $viajes;
	        $parametros['mensaje'] = 'Listado de viajes.';
	        $this->accesoAPaginaQueLista($parametros);
        }
    }

    public function accesoAPaginaQueLista($parametros){
        //Muestra la pagina con el listado de viajes
        $view = new Home();
        $arreglo = array();
        $arreglo['mensajeDeResultado'] = $parametros['mensaje'];
        if(isset($parametros['listaViajes'])){
            $arreglo['listadoCompletoDeViajes'] = $parametros['listaViajes'];
            $arreglo['elemPorPagina'] = int("3");
        }
        $view->listarViajesGenerales('index.html.twig',$arreglo);
    }
	
	public function publicarViajeOcasional($datos){
		 $this->mostrarMenuPrincipalSesion();
	}
}

