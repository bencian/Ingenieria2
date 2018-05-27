<?php






//NO TOCAR.

require_once('model/PDORepository.php');



class AppModel extends PDORepository {

    private static $instance;

       private function __construct() {
        
    }

	public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function validateLogin($datos) {
        $answer = $this->queryList("SELECT * FROM usuario where usuario=? and clave=? ;", [ $datos["nomUsr"], $datos["psw"]]);
		return $answer;
    }

	
	public function registrar($datos){
        $answer = $this->queryList("INSERT INTO usuario (nombre, apellido, email, password, fecha_nacimiento) VALUES (:nombre,:apellido,:email,:password,:fecha_nacimiento)" , [ "nombre" => $datos["nombre"], "apellido" => $datos["apellido"], "email" => $datos["email"], "password" => $datos["pass"], "fecha_nacimiento" => $datos["nacimiento"]]);
		return $answer;
    }

	public function existeMail($datos){
		$answer = $this->queryList("SELECT nombre FROM usuario where email=?;", [ $datos ]);
		return $answer;
	}
	
	public function getId($datos){
		$answer = $this->queryList("SELECT id FROM usuario where email=?;", [ $datos ]);
		return $answer;
	}
		
    public function getPerfil($datos){
		$answer = $this->queryList("SELECT * FROM usuario where id=?;", [ $datos ]);
		return $answer;
	}
	
    public function existeUsuario($mail,$contraseña){
        //Busca en la bd el usuario con mail y contraseña ingresado
        $answer = $this->queryList("SELECT id FROM usuario WHERE email=:mail AND password=:contra", ['mail'=>$mail,'contra'=>$contraseña]);
        return $answer;
    }


    public function tipos(){
        $answer = $this->queryList("SELECT * FROM tipo", []);
        return $answer;
    }

    public function existeTipo($datos){
        $answer = $this->queryList("SELECT nombre FROM tipo where id=?;", [ $datos ]);
        return $answer;
    }

    public function registrar_vehiculo($datos){
        $answer = $this->queryList("INSERT INTO vehiculo (asientos, marca, modelo, patente, color, tipo_id) VALUES (:asientos,:marca,:modelo,:patente,:color,:tipo)" , [ "asientos"=> $datos["asientos"], "marca" => $datos["marca"], "modelo" => $datos["modelo"], "patente" => $datos["patente"], "color" => $datos["color"], "tipo_id" => $datos["tipo"]]);
        return $answer;
    }

    public function validarMail($datos){
        if (isset($datos["email"])){
            $answer = $this->existeMail($datos["email"]);
            if ($answer) { 
                return false; 
            } else { 
                return true;
            }
        } else {
            return true;
        }

    }

    public function actualizar_usuario($datos){

        $consulta = "UPDATE usuario SET (";
        $args=[];
        if(isset($datos["email"])){
            $consulta.= "email = ? ,";
            $args["email"] = $datos["email"];
        }
        if(isset($datos["nombre"])){
            $consulta.= "nombre = ? ,";
            $args["nombre"] = $datos["nombre"];
        }
        if(isset($datos["apellido"])){
            $consulta.= "apellido = ? ,";
            $args["apellido"] = $datos["apellido"];
        }
        if(isset($datos["pass"])){
            $consulta.= "password = ? ,";
            $args["password"] = $datos["pass"];
        }
        if(isset($datos["nacimiento"])){
            $consulta.= "fecha_nacimiento = ? ,";
            $args["fecha_nacimiento"] = $datos["nacimiento"];
        }
        $args["id"] = $_SESSION["id"];
        $consulta.= substr($consulta, 0, -1);
        $consulta.=") WHERE id=?";

        $answer = $this->queryList($consulta,[ $args] );
        return $answer;
    }



    public function busqueta_completa($datos){
        $answer= $this->queryList("SELECT * FROM viajes WHERE id_origen=? AND id_destino=? AND fecha=?",[$datos["origen"], $datos["destino"], $datos["fecha"]]);
        return $answer;
    }

    public function busqueta_parcial($datos){
        $answer= $this->queryList("SELECT * FROM viajes WHERE id_origen=? AND fecha=?",[$datos["origen"], $datos["fecha"]]);
        return $answer;
    }
}
