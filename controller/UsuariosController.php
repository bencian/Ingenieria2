<?php

require_once('model/AppModel.php');



class UsuarioController {

	private static $instance;

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    private function __construct() {
        
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

}