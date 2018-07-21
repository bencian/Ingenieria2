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
        //$vector["nombre"] = $dato["nombre"];
        //$vector["email"] = $dato["email"];
        if(isset($_GET["act"])){
            $dato["act"]=$_GET["act"];
            echo self::getTwig()->render("perfil.html.twig", $dato);   //$vector
        } else {
            echo self::getTwig()->render("perfil.html.twig", $dato, array() );
        }
    }

    public function mostrarNombreAjeno($dato){
        if(isset($_GET["act"])){
            $dato["act"]=$_GET["act"];
            echo self::getTwig()->render("perfilAjeno.html.twig", $dato);
        } else {
            echo self::getTwig()->render("perfilAjeno.html.twig", $dato, array() );
        }
    }    

    public function formularioTipoVehiculos($datos,$string){
        echo self::getTwig()->render($string, array("tipoVehiculo" => $datos));
    }

    public function camposModificarPerfil($datos){
        echo self::getTwig()->render("modificar_perfil.html.twig", array("campoPerfil" => $datos));
    }

    public function listarViajes($datos, $ciudades, $busqueda, $ciudadesOrdenadas){
        if(isSet($_SESSION["id"])){
            echo self::getTwig()->render("listar_viajes.html.twig", array("viajes" => $datos, "ciudades"=> $ciudades, "usuario" => $_SESSION, "busqueda"=>$busqueda, "ciudadesOrdenadas"=>$ciudadesOrdenadas));
        } else {
            echo self::getTwig()->render("listar_viajes.html.twig", array("viajes" => $datos, "ciudades"=> $ciudades, "busqueda"=>$busqueda, "ciudadesOrdenadas"=>$ciudadesOrdenadas)); 
        }
    }

    public function listarVehiculosPropios($vehiculos){
        echo self::getTwig()->render("ver_vehiculos.html.twig", array("vehiculos" => $vehiculos));
    }

    public function modificarVehiculo($html,$datos){
        echo self::getTwig()->render($html, array("vehiculo" => $datos));
    }

    public function modificarViajeOcasional($viaje, $datos, $ciudadesOrdenadas){
        echo self::getTwig()->render("modificarViajeOcasional.html.twig", array("viaje" => $viaje, "vectorForm"=> $datos, "ciudadesOrdenadas"=>$ciudadesOrdenadas));
    }

    public function mostrarMenuSinSesion($html,$datos,$ciudades){
        echo self::getTwig()->render($html, array("datos" => $datos,"ciudades"=>$ciudades['ciudades'], "ciudadesOrdenadas"=>$ciudades['ciudadesOrdenadas']));
    }

    public function listarCiudadesMenuPrincipal($datos, $viajes, $ciudadesOrdenadas){
        echo self::getTwig()->render("sesion.html.twig", array("vectorForm" => $datos, "datos"=> $viajes, "ciudadesOrdenadas"=>$ciudadesOrdenadas));
    }

    public function eliminarEnCascada($parametros){
        echo self::getTwig()->render("eliminarEnCascada.html.twig", array("viajes"=>$parametros["viajes"], "vehiculo"=>$parametros["vehiculo"]));
    }

    public function verPublicacionViaje($viaje,$calificaciones,$vehiculo,$ciudades,$piloto,$postulado,$postulados,$cantidadAceptados, $datos, $preguntasYrespuestas){
        /* capaz conviene partir esta funcion en dos... 
        var_dump($postulado); */
        $cant_postulados=sizeof($postulados);
        if(isSet($_SESSION["id"])){
            echo self::getTwig()->render("verPublicacionViaje.html.twig", array("viaje"=>$viaje, "vehiculo"=>$vehiculo, "calificaciones"=>$calificaciones, "ciudades"=>$ciudades, "piloto"=>$piloto, "usuario"=>$_SESSION["id"], "postulado"=>$postulado, "postulados"=>$postulados, "cantPostulados"=>$cant_postulados, "cantidadAceptados"=>$cantidadAceptados, "busqueda"=>$datos, "preguntasYrespuestas"=>$preguntasYrespuestas));
        } else {
            echo self::getTwig()->render("verPublicacionViajeSinSesion.html.twig", array("viaje"=>$viaje, "vehiculo"=>$vehiculo, "calificaciones"=>$calificaciones, "ciudades"=>$ciudades, "piloto"=>$piloto, "cantPostulados"=>$cant_postulados, "busqueda"=>$datos, "preguntasYrespuestas"=>$preguntasYrespuestas));
        }
    }

    public function cancelarPostulacionAceptada($datos){
        echo self::getTwig()->render("cancelarPostulacionAceptada.html.twig", array("viaje"=>$datos));
    }

    public function eliminarViaje($datos,$cantidad){
        echo self::getTwig()->render("confirmacionEliminacionViaje.html.twig", array("viaje"=>$datos, "cantidadAceptados"=>$cantidad));
    }

    public function pagar(){
        echo self::getTwig()->render("pagar.html.twig", array());
    }

    public function listarViajesAPagar($viajes,$ciudades){
        echo self::getTwig()->render("lista_viajes_a_pagar.html.twig", array("viajes"=>$viajes, "ciudades"=>$ciudades));
    }

    public function pantallaParaPagar($viaje,$vehiculo){
        echo self::getTwig()->render("pagar_viaje.html.twig", array("viaje"=>$viaje,"vehiculo"=>$vehiculo));
    }

    public function calificacion($datos,$ciudades,$rol){
        echo self::getTwig()->render("calificar.html.twig", array("datos"=>$datos,"ciudades"=>$ciudades,"rol"=>$rol));
    }

}