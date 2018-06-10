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
}