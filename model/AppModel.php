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
        $answer = $this->queryDevuelveId("INSERT INTO vehiculo (asientos, marca, modelo, patente, color, tipo_id) VALUES (:asientos,:marca,:modelo,:patente,:color,:tipo_id)" , [ "asientos"=>$datos["asientos"], "marca"=>$datos["marca"], "modelo"=>$datos["modelo"], "patente"=>$datos["patente"], "color"=>$datos["color"], "tipo_id"=>$datos["tipo"]]);
		$datos2["usuario"] = $datos["id_usuario"];
		$datos2["vehiculo"] = $answer;
		$this->asociar_vehiculo($datos2);
		return $answer;
    }
	
	public function asociar_vehiculo($datos){
		$answer = $this->queryList("INSERT INTO usuario_has_vehiculo (usuarios_id, vehiculo_id) VALUES (:usuario, :vehiculo)" , [ "usuario"=>$datos["usuario"], "vehiculo"=>$datos["vehiculo"]]);
		return $answer;
	}

	public function actualizarUsuario($datos){
		 $answer = $this->queryList("UPDATE usuario SET nombre=:nombre, apellido=:apellido, email=:email, password=:password, fecha_nacimiento=:fecha_nacimiento WHERE id=:id", ["nombre" => $datos["nombre"], "apellido" => $datos["apellido"], "email" => $datos["email"], "password" => $datos["pass"], "fecha_nacimiento" => $datos["nacimiento"], "id" => $datos["id"]]);
		 return $answer;
	}


    /*public function actualizar_usuario($datos){


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

    }*/ //cambio esto para tener los campos viejos y actualizar todo

    public function getCiudad($datos){
        $answer= $this->queryList("SELECT id FROM ciudad WHERE nombre=?",[$datos]);
        return $answer;
    }

    public function busqueda_completa($datos){
        $origen= $this->getCiudad($datos["origen"]);
        $destino= $this->getCiudad($datos["destino"]);
        $fecha= $datos["salida"];
        /*$sql="SELECT * FROM viaje WHERE id_origen=".$origen[0]["id"]." AND id_destino=".$destino[0]["id"]." AND fecha=\'".$fecha. "';";
        $answer= $this->queryList($sql, []);
        var_dump($answer);*/

        $answer= $this->queryList("SELECT * FROM viaje WHERE id_origen=:origen AND id_destino=:destino AND fecha=:fecha", ["origen"=>$origen[0]["id"], "destino"=>$destino[0]["id"], "fecha"=>$fecha]);
        array_push($answer[0], $origen, $destino);
        var_dump($answer[0]);
        return $answer;
    }

    public function busqueda_parcial($datos){
        $origen= $this->getCiudad($datos["origen"]);
        $fecha= $datos["salida"];

        $answer= $this->queryList("SELECT * FROM viaje WHERE id_origen=:origen AND fecha=:fecha", ["origen"=>$origen[0]["id"], "fecha"=>$fecha]);
        return $answer;
    }

    public function getTipoVehiculo($datos){
        $answer= $this->queryList("SELECT nombre FROM tipo WHERE id=:id",["id"=>$datos]);
        return $answer;
    }

    public function getVehiculos(){
        $id=$_SESSION["id"];
        $answer= $this->queryList("SELECT * FROM vehiculo v INNER JOIN usuario_has_vehiculo uhv ON v.id=uhv.vehiculo_id WHERE usuarios_id=:usuario", [ "usuario"=>$_SESSION["id"]]);
        for ($i=0; $i < sizeof($answer) ; $i++) { 
            $a=$this->getTipoVehiculo($answer[$i]["tipo_id"])[0][0];
            $answer[$i]["tipo_id"]=$a;
        }
        return $answer;
    }

    public function poseeViajesEchos($datos){
        $answer= $this->queryList("SELECT * FROM vehiculo vh INNER JOIN viaje vj ON vh.id=vj.vehiculo_id WHERE vh.id=:vehiculo", [ "vehiculo"=>$datos["id"]]);
        return $answer;
    }

    public function eliminarViaje($idViaje){
        //Elimina el viaje con id pasado por parametro
        $this->queryList("DELETE FROM viaje WHERE id=:id_viaje", ['id_viaje'=>$idViaje]);
    }

    public function getViajes(){
        $answer = $this->queryList("SELECT * FROM viaje", []);
        return $answer;
    }
	
	public function getVehiculo($idVehiculo){
		$answer = $this->queryList("SELECT * FROM vehiculo WHERE id=?", [$idVehiculo]);
		return $answer;
	}
	
	public function actualizar_vehiculo($datos){
		$answer = $this->queryList("UPDATE vehiculo SET marca=:marca, modelo=:modelo, patente=:patente, color=:color, tipo_id=:tipo_id, asientos=:asientos  WHERE id=:id", ["marca" => $datos["marca"], "modelo" => $datos["modelo"], "patente" => $datos["patente"], "color" => $datos["color"], "tipo_id" => $datos["tipo"],"asientos" => $datos["asientos"] , "id" => $datos["id"]]);
		 return $answer;
	}

    public function eliminarRelacionUsuarioVehiculo($datos){
        $answer= $this->queryList("DELETE FROM usuario_has_vehiculo WHERE vehiculo_id =:vehiculo", [ "vehiculo"=>$datos["id"]]);
        return $answer;
    }
    
    public function borrarVehiculo($datos){
        $answer= $this->queryList("DELETE FROM vehiculo WHERE id =:vehiculo", [ "vehiculo"=>$datos["id"]]);
        return $answer;
    }
	
	public function getCiudades(){
		$answer = $this->queryList("SELECT * FROM ciudad", []);
		return $answer;
	}

    public function getViajeOcasional($datos){
        $answer = $this->queryList("SELECT * FROM viaje where id=?;", [ $datos ]);
        $tmp = $this->queryList("SELECT * FROM viaje_ocacional where viaje_id=?;", [ $datos ]);
        $answer["hora_salida"]=$tmp["hora_salida"];
        return $answer;
    }
	
	/*public function crearViajeOcasional($datos){
		$answer = $this->queryDevuelveId("INSERT INTO ", []);
	}*/

}


/* SELECT * FROM viaje WHERE id_origen='1' AND fecha='2018-05-10' */

