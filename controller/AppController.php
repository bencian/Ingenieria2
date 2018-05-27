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
			$view->show("sesion.html.twig");
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
			array_push($vector,$tipos[$i]["nombre"]);
		}
		$view->formularioTipoVehiculos($vector);
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
		if($anio-$tempArray[0]>14){
			if($anio-$tempArray[0]==15){
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
			echo "Necesitas tener al menos 15 años para registrarte al sitio ";
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
            		$view->show("sesion.html.twig"); //insertar pagina principal
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
				$bd->registrar_vehiculo($datos);
				$view->show("sesion.html.twig");
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
}

