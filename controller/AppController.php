<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */
 
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
        $view = new Home();
		$bd = AppModel::getInstance();
		$bd->getPerfil(23);
        $view->show("index.html.twig");
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
        $view->show("registrar_vehiculo.html.twig");
    }

	public function containsNumbers($String){
		return preg_match('/\\d/', $String) > 0;
	}
	
	public function mayorDeEdad($String){
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
}

