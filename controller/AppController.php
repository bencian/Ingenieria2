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
			$test = $this->validacion($datos);
			if(!($bd->existeMail($datos["email"]))&&($test)){
				$bd->registrar($datos);
				$view->show("index.html.twig");
			} else {
				echo "Error en la registracion";
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
			if($mes-$tempArray[1]>-1){
				if($dia-$tempArray[2]>-1){
					$bool = true;
				}
			}			
		}
		return $bool;
	}
	
	public function validacion($datos){
		$valor = (($datos["pass"]==$datos["pass1"])&&(strlen($datos["pass"])>7)&&(preg_match("#\W+#", $datos["pass"]))&&($this->containsNumbers($datos["pass"]))&&($this->mayorDeEdad($datos["nacimiento"])));
		return $valor;
	}
}

