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
		if(isset($datos)){
			$bd = AppModel::getInstance();
			var_dump($bd instanceof AppModel);
			var_dump($datos);
			$bd = registrar($datos);
		}
	}

    public function registrar_vehiculo(){
        $view = new Home();
        $view->show("registrar_vehiculo.html.twig");
    }

}

