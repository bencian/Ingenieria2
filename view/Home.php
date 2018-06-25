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
        echo self::getTwig()->render("perfil.html.twig", $dato );   //$vector
    }

    public function formularioTipoVehiculos($datos,$string){
        echo self::getTwig()->render($string, array("tipoVehiculo" => $datos));
    }

    public function camposModificarPerfil($datos){
        echo self::getTwig()->render("modificar_perfil.html.twig", array("campoPerfil" => $datos));
    }

    public function listarViajes($datos, $ciudades){
        echo self::getTwig()->render("listar_viajes.html.twig", array("viajes" => $datos, "ciudades"=> $ciudades));
    }

    public function listarVehiculosPropios($vehiculos){
        echo self::getTwig()->render("ver_vehiculos.html.twig", array("vehiculos" => $vehiculos));
    }

    public function modificarVehiculo($html,$datos){
        echo self::getTwig()->render($html, array("vehiculo" => $datos));
    }

    public function modificarViajeOcasional($viaje, $datos){
        echo self::getTwig()->render("modificarViajeOcasional.html.twig", array("viaje" => $viaje, "vectorForm"=> $datos));
    }

    public function mostrarMenuSinSesion($html,$datos,$ciudades){
        echo self::getTwig()->render($html, array("datos" => $datos,"ciudades"=>$ciudades['ciudades']));
    }

    public function listarCiudadesMenuPrincipal($datos, $viajes){
        echo self::getTwig()->render("sesion.html.twig", array("vectorForm" => $datos, "datos"=> $viajes));
    }

    public function eliminarEnCascada($parametros){
        echo self::getTwig()->render("eliminarEnCascada.html.twig", array("viajes"=>$parametros["viajes"], "vehiculo"=>$parametros["vehiculo"]));
    }

    public function verPublicacionViaje($viaje,$calificaciones,$vehiculo,$ciudades,$piloto,$postulado,$postulados){
        /* capaz conviene partir esta funcion en dos... 
        var_dump($postulado); */
        $cant_postulados=sizeof($postulados);
        if(isSet($_SESSION["id"])){
            echo self::getTwig()->render("verPublicacionViaje.html.twig", array("viaje"=>$viaje, "vehiculo"=>$vehiculo, "calificaciones"=>$calificaciones, "ciudades"=>$ciudades, "piloto"=>$piloto, "usuario"=>$_SESSION["id"], "postulado"=>$postulado, "postulados"=>$postulados, "cantPostulados"=>$cant_postulados));
        } else {
            echo self::getTwig()->render("verPublicacionViajeSinSesion.html.twig", array("viaje"=>$viaje, "vehiculo"=>$vehiculo, "calificaciones"=>$calificaciones, "ciudades"=>$ciudades, "piloto"=>$piloto, "cantPostulados"=>$cant_postulados));
        }
    }

    public function cancelarPostulacionAceptada($datos){
        var_dump($datos);
        echo self::getTwig()->render("cancelarPostulacionAceptada.html.twig", array("viaje"=>$datos));
    }
}