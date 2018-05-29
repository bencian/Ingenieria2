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
	
	 public function registrarVehiculo($dato){
    	echo self::getTwig()->render("registrar_vehiculo.html.twig", array("tiposVehiculos" => $dato));
    }
	
	public function mostrarNombre($dato){
		$vector["nombre"] = $dato["nombre"];
		$vector["email"] = $dato["email"];
		echo self::getTwig()->render("perfil.html.twig", $vector );
	}
	
	public function formularioTipoVehiculos($datos,$string){
		echo self::getTwig()->render($string, array("tipoVehiculo" => $datos));
	}

	public function camposModificarPerfil($datos){
		echo self::getTwig()->render("modificar_perfil.html.twig", array("campoPerfil" => $datos));
	}

	public function listarViajes($datos){
		echo self::getTwig()->render("listar_viajes.html.twig", array("viajes" => $datos));
	}

	public function listarVehiculosPropios($vehiculos){
		echo self::getTwig()->render("ver_vehiculos.html.twig", array("vehiculos" => $vehiculos));
	}
	
	public function modificarVehiculo($html,$datos){
		echo self::getTwig()->render($html, array("vehiculo" => $datos));
	}


	public function modificarViajeOcacional($viaje){
	/*"modificar_viaje_ocacional.html.twig";
	*/


	public function listarViajesGenerales($html,$datos){
		echo self::getTwig()->render($html, array("datos" => $datos));

	
	public function listarCiudadesMenuPrincipal($datos){
		echo self::getTwig()->render("sesion.html.twig", array("vectorForm" => $datos));

	}
}
