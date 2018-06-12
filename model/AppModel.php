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

    //VEHICULO
    public function existeTipo($datos){
        $answer = $this->queryList("SELECT nombre FROM tipo where id=?;", [ $datos ]);
        return $answer;
    }

    //VEHICULO
    public function registrar_vehiculo($datos){
        $answer = $this->queryDevuelveId("INSERT INTO vehiculo (asientos, marca, modelo, patente, color, tipo_id) VALUES (:asientos,:marca,:modelo,:patente,:color,:tipo_id)" , [ "asientos"=>$datos["asientos"], "marca"=>$datos["marca"], "modelo"=>$datos["modelo"], "patente"=>$datos["patente"], "color"=>$datos["color"], "tipo_id"=>$datos["tipo"]]);
        $datos2["usuario"] = $datos["id_usuario"];
        $datos2["vehiculo"] = $answer;
        $this->asociar_vehiculo($datos2);
        return $answer;
    }

    //VEHICULO
    public function eliminarRelacionUsuarioVehiculo($datos){
        $answer= $this->queryList("DELETE FROM usuario_has_vehiculo WHERE vehiculo_id =:vehiculo", [ "vehiculo"=>$datos["id"]]);
        return $answer;
    }

    //VEHICULO
    public function borrarVehiculo($datos){
        $answer= $this->queryList("DELETE FROM vehiculo WHERE id =:vehiculo", [ "vehiculo"=>$datos["id"]]);
        return $answer;
    }

    //VEHICULO
    public function getVehiculo($idVehiculo){
        $answer = $this->queryList("SELECT * FROM vehiculo WHERE id=?", [$idVehiculo]);
        return $answer;
    }

    //VEHICULO
    public function actualizar_vehiculo($datos){
        $answer = $this->queryList("UPDATE vehiculo SET marca=:marca, modelo=:modelo, patente=:patente, color=:color, tipo_id=:tipo_id, asientos=:asientos  WHERE id=:id", ["marca" => $datos["marca"], "modelo" => $datos["modelo"], "patente" => $datos["patente"], "color" => $datos["color"], "tipo_id" => $datos["tipo"],"asientos" => $datos["asientos"] , "id" => $datos["id"]]);
         return $answer;
    }

    //VEHICULO
    public function getAsientos($datos){
        $answer = $this->queryList("SELECT asientos FROM vehiculo where id=?",[$datos]);
        return $answer;
    }

}