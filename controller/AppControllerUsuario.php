<?php

/**
 * Description of ResourceController
 *
 * @author fede
 */

require_once('model/AppModel.php');

require_once('controller/AppController.php')


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
                $menu = AppController::getInstance();
                $menu->index();
            } else {
                if ($bdUsuario->existeMail($datos["email"])){
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
            $datosUsuario = AppModel::getInstance()->getPerfil($_SESSION['id']); //falta
            $nombre = $datosUsuario[0]["nombre"]." ".$datosUsuario[0]["apellido"];
            $mostrarDatos["nombre"] = $nombre;
            $mostrarDatos["email"] = $datosUsuario[0]["email"];
            $view = new Home();
            $viajes = AppModel::getInstance()->getViajesPropios($_SESSION['id']); //falta
            $mostrarDatos["viajes"]=$viajes;
            $view->mostrarNombre($mostrarDatos); //falta
        }
    }
}