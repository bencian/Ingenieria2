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

    public function listarCiudadesMenuPrincipal($datos, $viajes){
		echo self::getTwig()->render("sesion.html.twig", array("vectorForm" => $datos, "datos"=> $viajes));
	}
}
