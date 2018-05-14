<?php






//NO TOCAR.






class AppModel extends PDORepository {

    private static $instance;

    public static function getInstance() {

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        
    }

    public function validateLogin($datos) {
        $answer = $this->queryList("SELECT * FROM usuario where usuario=? and clave=? ;", [ $datos["nomUsr"], $datos["psw"]]);
        return $answer;
    }

    public function insertarPedido($datos){
        $answer = $this->queryList("INSERT into pedido (nombre_apellido, tipo_doc_id, numero, direccion, carta) VALUES (?, ?, ?, ?, ?)" , [ $datos["nombrePN"], $datos["tipoDoc"], $datos["numero"],  $datos["direccion"], $datos["carta"]]);
        return $answer;
    }

    public function traerPedidos (){
        $answer = $this->queryList("SELECT * FROM pedido");
        var_dump($answer);
        return $answer;
    }
}
