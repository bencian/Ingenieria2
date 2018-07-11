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
}