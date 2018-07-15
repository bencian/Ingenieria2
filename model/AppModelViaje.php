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
        $answer = $this->queryList("SELECT vj.id, vj.fecha, vj.origen_id, vj.destino_id, vj.precio, vj.hora_salida, vj.lugares FROM viaje vj WHERE vj.fecha<:fecha_futuro and vj.fecha>=:fecha_hoy order by vj.fecha", ["fecha_futuro"=>$dato, "fecha_hoy"=>$fecha]);
        return $answer;
    }    

    public function getCiudad($datos){
        $answer= $this->queryList("SELECT id FROM ciudad WHERE nombre=?",[$datos]);
        return $answer;
    }
    
    public function busqueda_completa($datos){
        $answer= $this->queryList("SELECT * FROM viaje WHERE origen_id=:origen AND destino_id=:destino AND fecha=:fecha", ["origen"=>$datos["origen"], "destino"=>$datos["destino"], "fecha"=>$datos["salida"]]);
        return $answer;
    }

    public function busqueda_parcial($datos){
        $answer= $this->queryList("SELECT * FROM viaje WHERE origen_id=:origen AND fecha=:fecha", ["origen"=>$datos["origen"], "fecha"=>$datos["salida"]]);
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

    public function eliminarViajeOcasional($idViaje){ //no sirve
        $this->queryList("DELETE FROM viaje_ocasional WHERE viaje_id=:id_viaje", ['id_viaje'=>$idViaje]);
    }
        
    public function eliminarViajePeriodico($idViaje){ //no sirve
        $this->queryList("DELETE FROM viaje_periodico WHERE viaje_id=:id_viaje", ['id_viaje'=>$idViaje]);
    }

    public function eliminarViajePeriodicoDias($idViaje){ //no sirve
        $this->queryList("DELETE FROM dia_horario WHERE viaje_periodico_viaje_id=:id_viaje", ['id_viaje'=>$idViaje]);
    }

    public function getViajeOcasional($datos){
        $answer = $this->queryList("SELECT * FROM viaje where id=?;", [ $datos["id"] ]);
        return $answer;
    }

    public function getViajeId($datos){
        $answer = $this->queryList("INSERT INTO viaje (fecha,precio,hora_salida,duracion,distancia,lugares,comentarios,origen_id,destino_id,usuario_id,vehiculo_id) VALUES (:fecha,:precio,:hora_salida,:duracion,:distancia,:lugares,:comentarios,:origen_id,:destino_id,:usuario_id,:vehiculo_id)", ["fecha"=>$datos["fecha"],"precio"=>$datos["precio"],"hora_salida"=>$datos["hora_salida"],"duracion"=>$datos["duracion"],"distancia"=>$datos["distancia"],"lugares"=>$datos["asientos"],"comentarios"=>$datos["comentarios"],"origen_id"=>$datos["origen"],"destino_id"=>$datos["destino"],"usuario_id"=>$_SESSION["id"],"vehiculo_id"=>$datos["vehiculo"]]);
        //$answer = $this->queryDevuelveId("INSERT INTO viaje (fecha,precio,hora_salida,duracion,distancia,lugares,comentarios,origen_id,destino_id,usuario_id,vehiculo_id) VALUES (:fecha,:precio,:duracion,:distancia,:lugares,:comentarios,:origen_id,:destino_id,:usuario_id,:vehiculo_id)", ["fecha"=>$datos["fecha"],"precio"=>$datos["precio"],"hora_salida"=>$datos["hora_salida"],"duracion"=>$datos["duracion"],"distancia"=>$datos["distancia"],"lugares"=>$datos["asientos"],"comentarios"=>$datos["comentarios"],"origen_id"=>$datos["origen"],"destino_id"=>$datos["destino"],"usuario_id"=>$_SESSION["id"],"vehiculo_id"=>$datos["vehiculo"] ]);
        return $answer;
    }

    public function crearOcasional($datos){ //no sirve
       $answer = $this->queryList("INSERT INTO viaje_ocasional (viaje_id, hora_salida) VALUES (:viaje_id, :hora_salida)", ["viaje_id"=>$datos["id_viaje"], "hora_salida"=>$datos["hora_salida"]]);
        return $answer;
    }

    public function asociarPeriodico($datos){ //no sirve
        $answer = $this->queryList("INSERT INTO viaje_periodico (viaje_id, fecha_fin) VALUES (:viaje_id, :hora_salida)",["viaje_id" => $datos["viajeId"], "hora_salida" => $datos["fechaFinal"]]);
        return $answer;
    }
    
    public function asociarDiaHorario($datos){ //no sirve
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
            WHERE (vh.id=:vehiculo AND uv.estado='Aceptado' AND (vj.fecha> CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME())) GROUP BY (vj.id)", [ "vehiculo"=>$datos["id"]]);
        return $answer;
    }

    public function eliminarViajesFuturosEnCascada($datos){
        /*$answer=$this->queryList("UPDATE usuario_viaje SET estado='Viaje eliminado' WHERE viaje_id IN (
            SELECT id FROM viaje 
            WHERE vehiculo_id=:vehiculo AND((fecha>CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME())))", ["vehiculo"=>$datos["id"]]); //ESTA ESTA BIEN

        $answer=$this->queryList("DELETE FROM usuario_viaje WHERE viaje_id IN (
            SELECT id FROM viaje
            INNER JOIN viaje_ocasional ON (viaje_ocasional.viaje_id=viaje.id) 
            WHERE vehiculo_id=:vehiculo AND ((fecha>CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME())))", ["vehiculo"=>$datos["id"]]);

        $answer=$this->queryList("DELETE FROM viaje_ocasional WHERE viaje_id IN (
            SELECT id FROM viaje
            INNER JOIN viaje_ocasional ON (viaje_ocasional.viaje_id=viaje.id) 
            WHERE vehiculo_id=:vehiculo AND ((fecha>CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME())))", ["vehiculo"=>$datos["id"]]);

        $answer=$this->queryList("DELETE v FROM viaje v  
        LEFT JOIN viaje_ocasional vo ON (vo.viaje_id= v.id)
        WHERE (vehiculo_id=:vehiculo AND vo.hora_salida is NULL)", ["vehiculo"=>$datos["id"]]);

        $answer=$this->queryList("UPDATE viaje v SET v.usuario_id=-1, v.vehiculo_id=-1
        LEFT JOIN viaje_ocasional vo ON (vo.viaje_id= v.id)
        WHERE (vehiculo_id=:vehiculo AND vo.hora_salida is NULL)", ["vehiculo"=>$datos["id"]]);        
        
        return $answer;*/
        //estamos en la Beeeeeeeeeeeeee
    }

    public function getViaje($viaje_id){
        $answer = $this->queryList("SELECT * FROM viaje where id=?;", [ $viaje_id["id"] ]);
        return $answer[0];
    }

    public function postularme($datos){
        $answer= $this->queryList("INSERT INTO usuario_viaje  (usuario_id, viaje_id, estado) VALUES (:usuario, :viaje, :estado)", ["usuario"=>$_SESSION["id"], "viaje"=>$datos["id"], "estado"=>'Pendiente']);
        return $answer;
    }

    public function yaMePostule($datos){
        $answer= $this->queryList("SELECT * FROM usuario_viaje WHERE (usuario_id=:usuario AND viaje_id=:viaje)", ["usuario"=>$_SESSION["id"], "viaje"=>$datos["id"]]);
        return $answer;
    }

    public function cancelarPostulacion($datos){
        $answer= $this->queryList("DELETE FROM usuario_viaje WHERE (usuario_id=:usuario AND viaje_id=:viaje)", ["usuario"=>$_SESSION["id"], "viaje"=>$datos["id"]]);
        return $answer;
    }

    public function actualizarViajeOcasional($datos){
        $answer = $this->queryList("UPDATE viaje SET fecha=:fecha, precio=:precio, duracion=:duracion, distancia=:distancia, lugares=:lugares, comentarios=:comentarios, origen_id=:origen_id, destino_id=:destino_id, vehiculo_id=:vehiculo_id, hora_salida=:hora WHERE id=:id;",[ "fecha"=>$datos["fecha"], "precio"=>$datos["precio"], "duracion"=>$datos["duracion"], "distancia"=>$datos["distancia"], "lugares"=>$datos["asientos"], "comentarios"=>$datos["comentarios"], "origen_id"=>$datos["origen"], "destino_id"=>$datos["destino"], "vehiculo_id"=>$datos["vehiculo"], "id"=>$datos["id"], "hora"=>$datos["hora_salida"]]);
        return $answer;
    }

    public function getViajesConPatenteFechaEnOtroMomento($patente, $datos){
        $answer = $this->queryList("SELECT vj.id FROM viaje vj INNER JOIN vehiculo vh ON (vj.vehiculo_id = vh.id) WHERE (vh.patente=:patente AND vj.id!=:id)",["patente"=>$patente, "id"=>$datos["id"]]);
        return $answer;
    }

    public function cambiarEstadoParaAceptado($idViaje, $postulado){
        $answer = $this->queryList("UPDATE usuario_viaje SET estado='Aceptado' WHERE (viaje_id=:viaje and usuario_id=:usr)",["viaje"=>$idViaje, "usr"=>$postulado]);
        return $answer;
    }
    
    public function getViajesConPatenteFecha($patente){
        $answer = $this->queryList("SELECT vj.id FROM viaje vj INNER JOIN vehiculo vh ON (vj.vehiculo_id = vh.id) WHERE (vh.patente=:patente)",["patente"=>$patente]);
        return $answer;
    }



    public function getHorariosViaje($id){
        $answer = $this->queryList("SELECT vj.hora_salida,vj.duracion,vj.fecha FROM viaje vj WHERE vj.id=?",[$id]);
        return $answer;
    }

    public function getPostulados($viaje_id){
        $answer= $this->queryList("SELECT * FROM usuario_viaje uv 
            INNER JOIN usuario us ON (uv.usuario_id=us.id) 
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
        $answer = $this->queryList("UPDATE usuario_viaje SET estado='Rechazado' WHERE (viaje_id=:viaje and usuario_id=:usr)",["viaje"=>$idViaje, "usr"=>$postulado]);
        return $answer;
    }
}