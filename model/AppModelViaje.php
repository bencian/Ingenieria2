<?php






//NO TOCAR.

require_once('model/PDORepository.php');



class AppModelViaje extends PDORepository {

    private static $instance;

       private function __construct() {
        
    }

	public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getViajes($dato){
        $answer = $this->queryList("SELECT id, fecha, id_origen, id_destino FROM viaje WHERE fecha<?", [$dato]);
        return $answer;
    }    
    
    public function existeMail($datos){
		$answer = $this->queryList("SELECT nombre FROM usuario where email=?;", [ $datos ]);
		return $answer;
	}

    public function getCiudad($datos){
        $answer= $this->queryList("SELECT id FROM ciudad WHERE nombre=?",[$datos]);
        return $answer;
    }
    
    public function busqueda_completa($datos){
        $origen= $this->getCiudad($datos["origen"]);
        $destino= $this->getCiudad($datos["destino"]);
        $fecha= $datos["salida"];
        $answer= $this->queryList("SELECT * FROM viaje WHERE id_origen=:origen AND id_destino=:destino AND fecha=:fecha", ["origen"=>$origen[0]["id"], "destino"=>$destino[0]["id"], "fecha"=>$fecha]);
        $answer[0]["origen"]=$origen;
        $answer[0]["destino"]=$destino;
        return $answer;
    }

    public function busqueda_parcial($datos){
        $origen= $this->getCiudad($datos["origen"]);
        $fecha= $datos["salida"];
        $answer= $this->queryList("SELECT * FROM viaje WHERE id_origen=:origen AND fecha=:fecha", ["origen"=>$origen[0]["id"], "fecha"=>$fecha]);
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

    public function eliminarViajeOcasional($idViaje){
        $this->queryList("DELETE FROM viaje_ocasional WHERE viaje_id=:id_viaje", ['id_viaje'=>$idViaje]);
    }
        
    public function eliminarViajePeriodico($idViaje){
        $this->queryList("DELETE FROM viaje_periodico WHERE viaje_id=:id_viaje", ['id_viaje'=>$idViaje]);
    }

    public function eliminarViajePeriodicoDias($idViaje){
        $this->queryList("DELETE FROM dia_horario WHERE viaje_periodico_viaje_id=:id_viaje", ['id_viaje'=>$idViaje]);
    }

    public function getViajeOcasional($datos){
        $answer = $this->queryList("SELECT * FROM viaje where id=?;", [ $datos["id"] ]);
        $tmp = $this->queryList("SELECT * FROM viaje_ocasional where viaje_id=?;", [$datos["id"]]);
        $answer["hora_salida"]= $tmp[0]["hora_salida"];
        return $answer;
    }

    public function getViajeId($datos){
        $answer = $this->queryDevuelveId("INSERT INTO viaje (fecha,precio,duracion,distancia,lugares,comentarios,id_origen,id_destino,usuarios_id,vehiculo_id) VALUES (:fecha,:precio,:duracion,:distancia,:lugares,:comentarios,:id_origen,:id_destino,:usuarios_id,:vehiculo_id)", ["fecha"=>$datos["fecha"],"precio"=>$datos["precio"],"duracion"=>$datos["duracion"],"distancia"=>$datos["distancia"],"lugares"=>$datos["asientos"],"comentarios"=>$datos["comentarios"],"id_origen"=>$datos["origen"],"id_destino"=>$datos["destino"],"usuarios_id"=>$_SESSION["id"],"vehiculo_id"=>$datos["vehiculo"] ]);
        return $answer;
    }

    public function crearOcasional($datos){
       $answer = $this->queryList("INSERT INTO viaje_ocasional (viaje_id, hora_salida) VALUES (:viaje_id, :hora_salida)", ["viaje_id"=>$datos["id_viaje"], "hora_salida"=>$datos["hora_salida"]]);
        return $answer;
    }

    public function asociarPeriodico($datos){
        $answer = $this->queryList("INSERT INTO viaje_periodico (viaje_id, fecha_fin) VALUES (:viaje_id, :hora_salida)",["viaje_id" => $datos["viajeId"], "hora_salida" => $datos["fechaFinal"]]);
        return $answer;
    }
    
    public function asociarDiaHorario($datos){
        $answer = $this->queryList("INSERT INTO dia_horario (dia, viaje_periodico_viaje_id, horario) VALUES (:dia, :viaje_periodico_viaje_id, :horario)", [ "dia" =>$datos["fecha"] , "viaje_periodico_viaje_id" => $datos["idViaje"], "horario" => $datos["horario"] ]);
    }

    public function noPoseeViajesFuturos($datos){
        $answer= $this->queryList("SELECT COUNT(*) FROM vehiculo vh INNER JOIN viaje vj ON vh.id=vj.vehiculo_id WHERE vh.id=:vehiculo AND vj.fecha> CURDATE()", [ "vehiculo"=>$datos["id"]]);
      return $answer;
    }



    public function eliminarViajesFuturosEnCascada($datos){
       /* $this->queryList("DELETE FROM viaje_ocasional WHERE vehiculo_id=:vehiculo", ["vehiculo"=>$datos["id"]]);
        $this->queryList("DELETE FROM viaje_periodico WHERE vehiculo_id=:vehiculo", ["vehiculo"=>$datos["id"]]);*/
var_dump($datos);
        $answer=$this->queryList("DELETE dh FROM viaje_periodico vp INNER JOIN dia_horario dh ON (vp.viaje_id= dh.viaje_periodico_viaje_id) WHERE vp.viaje_id IN (SELECT id FROM viaje WHERE vehiculo_id=:vehiculo AND fecha>CURDATE());", ["vehiculo"=>$datos["id"]]);
        
        $answer=$this->queryList("DELETE FROM viaje_periodico WHERE viaje_id IN (SELECT id FROM viaje WHERE vehiculo_id=:vehiculo AND fecha>CURDATE())", ["vehiculo"=>$datos["id"]]);
        
        $answer=$this->queryList("DELETE FROM viaje_ocasional WHERE viaje_id IN (SELECT id FROM viaje WHERE vehiculo_id=:vehiculo AND fecha>CURDATE())", ["vehiculo"=>$datos["id"]]);
        
        $answer=$this->queryList("DELETE FROM viaje WHERE vehiculo_id=:vehiculo AND fecha>CURDATE()", ["vehiculo"=>$datos["id"]]);
        
        var_dump($answer);
        return $answer;

        /*SELECT * FROM viaje WHERE vehiculo_id=4 AND viaje.fecha> CURDATE()

        SELECT * FROM viaje_ocasional vp INNER JOIN  viaje v ON viaje_id=id WHERE vehiculo_id=4 AND fecha>CURDATE()
        */
        /*HAY QUE ELIMINAR VIAJES A PARTIR DEL MOMENTO ACTUAL*/
    }


    public function getViaje($viaje_id){
        $viaje = ($this->queryList("SELECT * FROM viaje where id=?;", [ $viaje_id["id"] ]))[0];
        $answer["viaje"]=$viaje;
        $ocasional = ($this->queryList("SELECT * FROM viaje_ocasional where viaje_id=?;", [$viaje_id["id"]]));
        if(!$ocasional){
            $periodico=($this->queryList("SELECT * FROM viaje_periodico where viaje_id=?;", [$viaje_id["id"]]));
            $diaHora=($this->queryList("SELECT * FROM dia_horario where viaje_periodico_viaje_id=?;", [$viaje_id["id"]]));
            $answer["periodico"]=$periodico[0];
            $answer["diaHora"]=$diaHora[0];
        } else {
            $answer["ocasional"]=$ocasional[0];
        }
        return $answer;
    }
}