<?php

/**
 * Description of SimpleResourceList
 *
 * @author fede
 */


class Home extends TwigView {
    
    public function show($pagina) {
        
        echo self::getTwig()->render($pagina); 
    }

    public function mostrarMenuSinSesion($html,$datos,$ciudades){
		echo self::getTwig()->render($html, array("datos" => $datos,"ciudades"=>$ciudades));
	}

	public function errorLogin($dato){
    	echo self::getTwig()->render("login.html.twig", array("errorTipo" => $dato));
    }

}
