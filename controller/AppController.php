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
		$valor = (($datos["pass"]==$datos["pass1"])&&(strlen($datos["pass"])>7)&&((preg_match("#\W+#", $datos["pass"]))or($this->containsNumbers($datos["pass"])))&&($this->mayorDeEdad($datos["nacimiento"])));
		if(!($datos["pass"]==$datos["pass1"])){
			echo "Las contraseñas no coinciden ";
		}
		if(!(strlen($datos["pass"])>7)){
			echo "La contraseña es muy corta ";
		}
		if(!((preg_match("#\W+#", $datos["pass"]))or($this->containsNumbers($datos["pass"])))){
			echo "La contraseña no contiene un simbolo o un numero ";			
		}
		if(!($this->mayorDeEdad($datos["nacimiento"]))){
			echo "Necesitas tener al menos 15 años para registrarte al sitio ";
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
        /*if(!isset($_SESSION)){
			session_start();
		}
        session_destroy();
        if(isset($_SESSION['id'])){
            session_unset();
        }*/
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
			$view = new Home();
			$view->mostrarNombre($nombre);
		}
	}

	public function crear_vehiculo($datos){
		$bd = AppModel::getInstance();
		$view = new Home();
		if(isset($datos)){
			$test = $this->validar_vehiculo($datos);
			if(($bd->existeTipo($datos["tipo"]))&&($test)&&preg_match("#[1-9][0-9]#",$datos["asientos"])){
				$bd->registrar_vehiculo($datos);
				$view->show("sesion.html.twig");
			} else {
				$view->show("registrar_vehiculo.html.twig");
			}
		}
	}

	public function validar_vehiculo($datos){
		$valor = ((preg_match("#[A-Za-z]{2}[0-9]{3}[A-Za-z]{2}|[A-Za-z]{3}[0-9]{3}#", $datos["patente"])) && (preg_match("#[1-2][0-9]{3}#", $datos["modelo"])));
		return $valor;
	}

}
