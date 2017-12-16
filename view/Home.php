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

    public function errorLogin($dato){
    	echo self::getTwig()->render("login.html.twig", array("errorTipo" => $dato));
    }

    public function errorForm($dato){
    	echo self::getTwig()->render("formPN.html.twig", array("errorTipo" => $dato));
    }
}
