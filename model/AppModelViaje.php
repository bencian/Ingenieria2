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
        date_default_timezone_set("America/Argentina/Buenos_Aires");
        $fecha = date('Y-m-d');
        $answer = $this->queryList("SELECT vj.id, vj.fecha, vj.id_origen, vj.id_destino, vj.precio, vo.hora_salida FROM viaje vj inner join viaje_ocasional vo on (vj.id = vo.viaje_id) WHERE vj.fecha<:fecha_futuro and vj.fecha>=:fecha_hoy order by vj.fecha", ["fecha_futuro"=>$dato, "fecha_hoy"=>$fecha]);
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
        $answer= $this->queryList("SELECT * FROM viaje INNER JOIN viaje_ocasional ON (viaje.id=viaje_ocasional.viaje_id) WHERE id_origen=:origen AND id_destino=:destino AND fecha=:fecha", ["origen"=>$origen[0]["id"], "destino"=>$destino[0]["id"], "fecha"=>$fecha]);
        $answer[0]["origen"]=$origen;
        $answer[0]["destino"]=$destino;
        return $answer;
    }

    public function busqueda_parcial($datos){
        $origen= $this->getCiudad($datos["origen"]);
        $fecha= $datos["salida"];
        $answer= $this->queryList("SELECT * FROM viaje INNER JOIN viaje_ocasional ON (viaje.id=viaje_ocasional.viaje_id) WHERE id_origen=:origen AND fecha=:fecha", ["origen"=>$origen[0]["id"], "fecha"=>$fecha]);
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
        $answer= $this->queryList("SELECT COUNT(*) FROM vehiculo vh 
            INNER JOIN viaje vj ON vh.id=vj.vehiculo_id 
            WHERE vh.id=:vehiculo AND vj.fecha> CURDATE()", [ "vehiculo"=>$datos["id"]]);
        return $answer;
    }

    public function viajesConAceptados($datos){
        $answer= $this->queryList("SELECT COUNT(vj.id) FROM vehiculo vh 
            INNER JOIN viaje vj ON (vh.id=vj.vehiculo_id) 
            INNER JOIN usuario_viaje uv ON (vj.id=uv.viaje_id) 
            INNER JOIN viaje_ocasional vo ON (vo.viaje_id=vj.id)
            WHERE (vh.id=:vehiculo AND uv.estado='Aceptado' AND (vj.fecha> CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME())) GROUP BY (vj.id)", [ "vehiculo"=>$datos["id"]]);
        return $answer;
    }

    public function eliminarViajesFuturosEnCascada($datos){
        $answer=$this->queryList("UPDATE usuario_viaje SET estado='Viaje eliminado' WHERE viaje_id IN (
            SELECT id FROM viaje
            INNER JOIN viaje_ocasional ON (viaje_ocasional.viaje_id=viaje.id) 
            WHERE vehiculo_id=:vehiculo AND ((fecha>CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME())))", ["vehiculo"=>$datos["id"]]);

        /*$answer=$this->queryList("DELETE FROM usuario_viaje WHERE viaje_id IN (
            SELECT id FROM viaje
            INNER JOIN viaje_ocasional ON (viaje_ocasional.viaje_id=viaje.id) 
            WHERE vehiculo_id=:vehiculo AND ((fecha>CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME())))", ["vehiculo"=>$datos["id"]]);*/

        $answer=$this->queryList("DELETE FROM viaje_ocasional WHERE viaje_id IN (
            SELECT id FROM viaje
            INNER JOIN viaje_ocasional ON (viaje_ocasional.viaje_id=viaje.id) 
            WHERE vehiculo_id=:vehiculo AND ((fecha>CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME())))", ["vehiculo"=>$datos["id"]]);

        $answer=$this->queryList("DELETE v FROM viaje v  
        LEFT JOIN viaje_ocasional vo ON (vo.viaje_id= v.id)
        WHERE (vehiculo_id=:vehiculo AND vo.hora_salida is NULL)", ["vehiculo"=>$datos["id"]]);
        
        return $answer;
    }

    public function getViaje($viaje_id){
        $viaje = $this->queryList("SELECT * FROM viaje where id=?;", [ $viaje_id["id"] ]);
        $answer["viaje"]=$viaje[0];
        $ocasional = ($this->queryList("SELECT * FROM viaje_ocasional where viaje_id=?;", [$viaje_id["id"]]));
        $answer["ocasional"]=$ocasional[0];
        return $answer;
    }

    public function postularme($datos){
        $answer= $this->queryList("INSERT INTO usuario_viaje  (usuarios_id, viaje_id, estado) VALUES (:usuario, :viaje, :estado)", ["usuario"=>$_SESSION["id"], "viaje"=>$datos["id"], "estado"=>'Pendiente']);
        return $answer;
    }

    public function yaMePostule($datos){
        $answer= $this->queryList("SELECT * FROM usuario_viaje WHERE (usuarios_id=:usuario AND viaje_id=:viaje)", ["usuario"=>$_SESSION["id"], "viaje"=>$datos["id"]]);
        return $answer;
    }

    public function cancelarPostulacion($datos){
        $answer= $this->queryList("DELETE FROM usuario_viaje WHERE (usuarios_id=:usuario AND viaje_id=:viaje)", ["usuario"=>$_SESSION["id"], "viaje"=>$datos["id"]]);
        return $answer;
    }

    public function actualizarViajeOcasional($datos, $asientos){
        $answer = $this->queryList("UPDATE viaje SET fecha=:fecha, precio=:precio, duracion=:duracion, distancia=:distancia, lugares=:lugares, comentarios=:comentarios, id_origen=:id_origen, id_destino=:id_destino, vehiculo_id=:vehiculo_id WHERE id=:id;",[ "fecha"=>$datos["fecha"], "precio"=>$datos["precio"], "duracion"=>$datos["duracion"], "distancia"=>$datos["distancia"], "lugares"=>$asientos[0][0], "comentarios"=>$datos["comentarios"], "id_origen"=>$datos["origen"], "id_destino"=>$datos["destino"], "vehiculo_id"=>$datos["vehiculo"], "id"=>$datos["id"]]);
        $answer2 = $this->queryList("UPDATE viaje_ocasional SET hora_salida=:hora WHERE viaje_id=:id",["hora"=>$datos["hora_salida"], "id"=>$datos["id"]]);
        return $answer;
    }

    public function cambiarEstadoParaAceptado($idViaje, $postulado){
        $answer = $this->queryList("UPDATE usuario_viaje SET estado='Aceptado' WHERE (viaje_id=:viaje and usuarios_id=:usr)",["viaje"=>$idViaje, "usr"=>$postulado]);
        return $answer;
    }
    
    public function getViajesConPatenteFecha($patente,$fecha){
        $answer = $this->queryList("SELECT vj.id FROM viaje vj INNER JOIN vehiculo vh ON (vj.vehiculo_id = vh.id) WHERE (vh.patente=:patente AND vj.fecha=:fecha)",["patente"=>$patente,"fecha"=>$fecha]);
        return $answer;
    }

    public function getHorariosViaje($id){
        $answer = $this->queryList("SELECT vo.hora_salida,vj.duracion FROM viaje vj INNER JOIN viaje_ocasional vo ON (vo.viaje_id = vj.id) WHERE vj.id=?",[$id]);
        return $answer;
    }

    public function getPostulados($viaje_id){
        $answer= $this->queryList("SELECT * FROM usuario_viaje uv 
            INNER JOIN usuario us ON (uv.usuarios_id=us.id) 
            WHERE (viaje_id=:viaje)", ["viaje"=>$viaje_id["id"]]);
        return $answer;
    }

    public function aceptadosParaEsteViaje($viajeId){
        $answer = $this->queryList("SELECT * FROM usuario_viaje uv
            WHERE (uv.viaje_id=:viaje and uv.estado='Aceptado')", ["viaje"=>$viajeId]);
        return $answer;
    }

    public function contarAceptados($viaje_id){
        $answer= $this->queryList("SELECT COUNT(*) FROM usuario_viaje uv WHERE (viaje_id=:viaje AND estado=:estado)", ["viaje"=>$viaje_id["id"], "estado"=>'Aceptado']);
        return $answer;
    }

    public function cambiarEstadoARechazado($idViaje, $postulado){
        $answer = $this->queryList("UPDATE usuario_viaje SET estado='Rechazado' WHERE (viaje_id=:viaje and usuarios_id=:usr)",["viaje"=>$idViaje, "usr"=>$postulado]);
        return $answer;
    }
}