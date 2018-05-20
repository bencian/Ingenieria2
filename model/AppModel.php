<?php






//NO TOCAR.





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

    public function insertarPedido($datos){
        $answer = $this->queryList("INSERT into pedido (nombre_apellido, tipo_doc_id, numero, direccion, carta) VALUES (?, ?, ?, ?, ?)" , [ $datos["nombrePN"], $datos["tipoDoc"], $datos["numero"],  $datos["direccion"], $datos["carta"]]);
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
	
    public function traerPedidos (){
        $answer = $this->queryList("SELECT * FROM pedido");
        var_dump($answer);
        return $answer;
    }
}
