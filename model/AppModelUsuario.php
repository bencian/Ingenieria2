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
        $answer = $this->queryList("INSERT INTO usuario (nombre, apellido, email, password, fecha_nacimiento) VALUES (:nombre,:apellido,:email,:password,:fecha_nacimiento)" , [ "nombre" => $datos["nombre"], "apellido" => $datos["apellido"], "email" => $datos["email"], "password" => $datos["pass"], "fecha_nacimiento" => $datos["nacimiento"]]);
        return $answer;
    }

    public function getId($datos){
        $answer = $this->queryList("SELECT id FROM usuario where email=?;", [ $datos ]);
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
        $answer = $this->queryList("SELECT * FROM viaje vj INNER JOIN viaje_ocasional vo ON vj.id=vo.viaje_id WHERE usuarios_id=:id", ["id"=>$_SESSION["id"]]);
        if($answer){
            return $answer;
        } else {
            $sql="SELECT * FROM viaje vj 
            INNER JOIN viaje_periodico vp ON vj.id=vp.viaje_id 
            INNER JOIN dia_horario dh ON dh.viaje_periodico_viaje_id=vj.id 
            WHERE id_usuario=:id";
            $answer = $this->queryList($sql, ["id"=>$id]);
            return $answer;
        }        
    }

    public function actualizarUsuario($datos){
        $answer = $this->queryList("UPDATE usuario SET nombre=:nombre, apellido=:apellido, email=:email, password=:password, fecha_nacimiento=:fecha_nacimiento WHERE id=:id", ["nombre" => $datos["nombre"], "apellido" => $datos["apellido"], "email" => $datos["email"], "password" => $datos["pass"], "fecha_nacimiento" => $datos["nacimiento"], "id" => $datos["id"]]);
        return $answer;
    }    
}