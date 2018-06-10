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

    public function getCiudades(){
		$answer = $this->queryList("SELECT * FROM ciudad", []);
		return $answer;
	}
	
	//VEHICULO
	public function getTipoVehiculo($datos){
        $answer= $this->queryList("SELECT nombre FROM tipo WHERE id=:id",["id"=>$datos]);
        return $answer;
    }

    //VEHICULO igual aca creo que voy a poner los for y eso en el controller, y mandar la query posta a AppModelUsuario
    public function getVehiculos(){
        $id=$_SESSION["id"];
        $answer= $this->queryList("SELECT * FROM vehiculo v INNER JOIN usuario_has_vehiculo uhv ON v.id=uhv.vehiculo_id WHERE usuarios_id=:usuario", [ "usuario"=>$_SESSION["id"]]);
        for ($i=0; $i < sizeof($answer) ; $i++) { 
            $a=$this->getTipoVehiculo($answer[$i]["tipo_id"])[0][0];
            $answer[$i]["tipo_id"]=$a;
        }
        return $answer;
    }

    //VEHICULO
    public function tipos(){
        $answer = $this->queryList("SELECT * FROM tipo", []);
        return $answer;
    }
}