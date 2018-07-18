<?php






//NO TOCAR.

require_once('model/PDORepository.php');



class AppModelUsuario extends PDORepository {

    private static $instance;

       private function __construct() {
        
    }

	public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function registrar($datos){
        $answer = $this->queryList("INSERT INTO usuario (nombre, apellido, calificacion_copiloto, calificacion_piloto, email, password, fecha_nacimiento) VALUES (:nombre,:apellido,0,0,:email,:password,:fecha_nacimiento)" , [ "nombre" => $datos["nombre"], "apellido" => $datos["apellido"], "email" => $datos["email"], "password" => $datos["pass"], "fecha_nacimiento" => $datos["nacimiento"]]);
        return $answer;
    }

    public function getId($datos){
        $answer = $this->queryList("SELECT id FROM usuario where email=?;", [ $datos ]);
        return $answer;
    }

    public function existeMail($datos){
        $answer = $this->queryList("SELECT nombre FROM usuario where email=?;", [ $datos ]);
        return $answer;
    }

    public function existeUsuario($mail,$contraseña){
        //Busca en la bd el usuario con mail y contraseña ingresado
        $answer = $this->queryList("SELECT id FROM usuario WHERE email=:mail AND password=:contra", ['mail'=>$mail,'contra'=>$contraseña]);
        return $answer;
    }

    public function getPerfil($datos){
        $answer = $this->queryList("SELECT * FROM usuario where id=?;", [ $datos ]);
        return $answer;
    }

    public function getViajesPropios($id){
        date_default_timezone_set("America/Argentina/Buenos_Aires");
        $fecha = date('Y-m-d');
        $answer = $this->queryList("SELECT * FROM viaje vj WHERE usuario_id=:id and vj.fecha>=:fecha", ["id"=>$_SESSION["id"],"fecha"=>$fecha]);
        return $answer;        
    }

    public function getViajesPiloto($id){
        date_default_timezone_set("America/Argentina/Buenos_Aires");
        $fecha = date('Y-m-d');
        $answer = $this->queryList("SELECT * FROM viaje vj WHERE usuario_id=:id and vj.fecha<:fecha", ["id"=>$_SESSION["id"],"fecha"=>$fecha]);
        return $answer;        
    }

    public function getViajesCopiloto($id){
        //falta el estado del viaje, que sea finalizado
        //solucionado con la fecha
        $answer = $this->queryList("SELECT * FROM usuario_viaje uv
        INNER JOIN viaje v ON (uv.viaje_id=v.id)
        WHERE (uv.usuario_id=:id AND (fecha<CURDATE()))", [ "id"=>$_SESSION["id"]]);
        return $answer;      
    }

    public function actualizarUsuario($datos){
        $answer = $this->queryList("UPDATE usuario SET nombre=:nombre, apellido=:apellido, email=:email, password=:password, fecha_nacimiento=:fecha_nacimiento WHERE id=:id", ["nombre" => $datos["nombre"], "apellido" => $datos["apellido"], "email" => $datos["email"], "password" => $datos["pass"], "fecha_nacimiento" => $datos["nacimiento"], "id" => $datos["id"]]);
        return $answer;
    }    

    public function getMisPostulaciones($usuario){
        $answer = $this->queryList("SELECT * FROM usuario_viaje uv
        INNER JOIN viaje v ON (uv.viaje_id=v.id)
        WHERE (uv.usuario_id=:id AND ((fecha>CURDATE()) OR (fecha=CURDATE() AND hora_salida>CURTIME() )))", [ "id"=>$usuario ]);
        return $answer;
    }

    public function publicarPregunta($datos){
        $answer = $this->queryList("INSERT INTO pregunta (pregunta, viaje_id, usuario_id) VALUES (:pregunta, :viaje, :usuario)", ["pregunta"=>$datos["pregunta"], "usuario"=>$datos["usuario"], "viaje"=>$datos["id"] ]);
        return $answer;
    }

    public function preguntasYRespuestas($viaje){
        $answer = $this->queryList("SELECT p.id, p.pregunta, p.viaje_id, p.usuario_id, r.respuesta, r.pregunta_id, u.nombre, u.apellido, u.email 
        FROM pregunta p
        INNER JOIN usuario u ON u.id=p.usuario_id
        LEFT JOIN respuesta r ON p.id=r.pregunta_id
        WHERE p.viaje_id=:viaje", ["viaje"=>$viaje ]);
        return $answer;
    }

    public function publicarRespuesta($datos){
        $answer = $this->queryList("INSERT INTO respuesta (pregunta_id, respuesta) 
            VALUES (:pregunta_id, :respuesta)", ["pregunta_id"=>$datos["pregunta_id"], "respuesta"=>$datos["respuesta"]]);
        return $answer;
    }

    public function calificacionPiloto($id){
        $answer = $this->queryList("SELECT calificacion_piloto FROM usuario WHERE id=:id",["id" => $id]);
        return $answer[0];
    }

    public function viajesHechosComoPiloto($id){
        $answer = $this->queryList("SELECT count(viaje.id) FROM viaje WHERE usuario_id=:id AND ((fecha<CURDATE()) OR (fecha=CURDATE() AND hora_salida<CURTIME()))",["id"=> $id]);
        return $answer[0];
    }

    public function calificacionCopiloto($id){
        $answer = $this->queryList("SELECT calificacion_copiloto FROM usuario WHERE id=:id",["id" => $id]);
        return $answer[0];
    }

    public function viajesHechosComoCopiloto($id){
        $answer = $this->queryList("SELECT count(v.id) FROM usuario_viaje uv
        INNER JOIN viaje v ON (uv.viaje_id=v.id)
        WHERE (uv.usuario_id=:id AND (uv.estado='Aceptado') AND ((fecha<CURDATE()) OR (fecha=CURDATE() AND hora_salida<CURTIME() )))", [ "id" => $id ]);
        return $answer[0];
    }

    public function pilotosACalificar($id){
        $answer = $this->queryList("SELECT *
            FROM viaje v INNER JOIN usuario_viaje uv on (uv.viaje_id=v.id) INNER JOIN usuario u on (v.usuario_id=u.id)
            WHERE ((fecha<CURDATE()) OR (fecha=CURDATE() AND date_add(CONCAT(fecha,' ',hora_salida),interval duracion HOUR)<NOW())) and uv.usuario_id=:id and uv.estado='Aceptado' and not exists (
            select * from calificacion_piloto where copiloto_califica=:id  and viaje_id = v.id)",["id" => $id]);
        return $answer;
    }

    public function copilotosACalificar($id){
        $answer = $this->queryList("SELECT u.email, uv.usuario_id, v.id, v.origen_id, v.destino_id, v.fecha, v.hora_salida, v.precio, v.duracion, v.distancia
            FROM viaje v INNER JOIN usuario_viaje uv on (uv.viaje_id=v.id) INNER JOIN usuario u on (uv.usuario_id=u.id)
            WHERE ((fecha<CURDATE()) OR (fecha=CURDATE() AND date_add(CONCAT(fecha,' ',hora_salida),interval duracion HOUR)<NOW())) and v.usuario_id=:id and not exists (
            select * from calificacion_copiloto where piloto_califica=:id  and viaje_id = v.id)",["id" => $id]);
        return $answer;
    }

    public function tengoViajesAPagar(){
        $answer = $this->queryList("SELECT COUNT(v.id)
            FROM viaje v
            WHERE ((v.fecha<CURDATE()) OR (v.fecha=CURDATE() AND date_add(CONCAT(fecha,' ',hora_salida),interval duracion HOUR)<NOW())) and v.usuario_id=:id AND pagado=0",["id" => $_SESSION["id"]]);
        return $answer;
    }

    public function listaViajesAPagar(){
        $answer = $this->queryList("SELECT *
            FROM viaje v
            INNER JOIN vehiculo vh ON vh.id=v.vehiculo_id
            WHERE ((v.fecha<CURDATE()) OR (v.fecha=CURDATE() AND date_add(CONCAT(fecha,' ',hora_salida),interval duracion HOUR)<NOW())) and v.usuario_id=:id AND pagado=0",["id" => $_SESSION["id"]]);
        return $answer;
    }

    public function getDatosCalifcacionPiloto($datos){
        $answer = $this->queryList("SELECT * 
            FROM viaje v INNER JOIN ",[]);
        return $answer;
    }

    public function getDatosCalifcacionCopiloto($datos){
        $answer = $this->queryList("SELECT u.email, uv.usuario_id, v.id, v.origen_id, v.destino_id, v.fecha, v.hora_salida, v.precio, v.duracion, v.distancia
            FROM viaje v INNER JOIN usuario_viaje uv on (uv.viaje_id=v.id) INNER JOIN usuario u on (uv.usuario_id=u.id) 
            WHERE (v.id=:viaje_id AND uv.usuario_id=:usuario_id)",["viaje_id"=>$datos["viaje_id"],"usuario_id"=>$datos["usuario_id"]]);
        return $answer;
    }

    public function calificarCopiloto($datos){
        $answer = $this->queryList("INSERT INTO calificacion_copiloto (puntuacion, comentarios, fecha, copiloto_calificado, viaje_id, piloto_califica) 
            VALUES (:puntuacion, :comentarios, CURDATE(), :copiloto, :viaje_id, :piloto)", ["puntuacion"=>$datos["puntaje"],"comentarios"=>$datos["comentarios"],"copiloto"=>$datos["usuario_id"],"viaje_id"=>$datos["viaje_id"],"piloto"=>$_SESSION["id"]]);

    public function validadorDeTarjetas($datos){
        $answer = $this->queryList("SELECT id
            FROM banco
            WHERE nombre=:nombre AND numero=:numero AND codigo=:codigo AND vencimiento=:vencimiento",["nombre" => $datos["nombre"], "numero"=> $datos["tarjeta"], "codigo"=> $datos["codigo"], "vencimiento"=> $datos["vencimiento"]]);
        return $answer;
    }

    public function setearPagado($id){
        $answer = $this->queryList("UPDATE viaje SET pagado=1 WHERE id=:id", ["id" => $id]);
        return $answer;
    }
}


