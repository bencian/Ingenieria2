<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
session_set_cookie_params(360000000,"/");
session_start();

require_once('controller/AppController.php');
require_once('controller/AppControllerViajes.php');
require_once('controller/AppControllerUsuario.php');
require_once('controller/AppControllerVehiculo.php');

require_once('model/PDORepository.php');
require_once('model/AppModel.php');
require_once('model/AppModelUsuario.php');
require_once('model/AppModelViaje.php');

require_once('view/TwigView.php');
require_once('view/Home.php');/*
require_once('controller/UsuariosController.php');
require_once('controller/ViajeController.php');*/

$controller = AppController::getInstance();
$usuario = AppControllerUsuario::getInstance();
$vehiculo = AppControllerVehiculo::getInstance();
$viaje = AppControllerViajes::getInstance();
if(!isset($_GET["action"])){
	$controller->index();
} elseif ($_GET["action"] == "login"){
	$usuario->login($_POST);
} elseif ($_GET["action"] == "registrarse"){
	$usuario->registrarse($_POST);
} elseif ($_GET["action"] == "registrar_vehiculo"){
	$vehiculo->registrar_vehiculo();
} elseif ($_GET["action"] == "crear_usuario"){
	$usuario->crear_usuario($_POST);
} elseif ($_GET["action"] == "nueva_Sesion"){
	$controller->validar_Inicio_Sesion($_POST);
} elseif ($_GET["action"] == "cerrar_sesion"){
	$controller->cerrarSesion();
} elseif ($_GET["action"] == "mostrar_perfil"){
	$usuario->mostrarPerfil();
} elseif ($_GET["action"] == "crear_vehiculo"){
	$vehiculo->crear_vehiculo($_POST);
} elseif ($_GET["action"] == "modificar_perfil"){
	$usuario->modificar_perfil();
} elseif ($_GET["action"] == "actualizar_perfil"){
	$usuario->actualizar_perfil($_POST);
} elseif ($_GET["action"] == "buscando"){
	$viaje->buscador($_POST);
} elseif ($_GET["action"] == "lista_vehiculos"){
	$vehiculo->listar_vehiculos($_GET);
} elseif ($_GET["action"] == "eliminar_vehiculo"){
	$vehiculo->eliminar_vehiculo($_POST);
} elseif ($_GET['action'] == "eliminar_viaje"){
    if(isset($_POST['id'])){
        if(is_numeric($_POST['id'])){
            $viaje->eliminarViaje($_POST['id']);
        } else {
            $usuario->mostrarPerfil();
        }
    } else {
    	$usuario->mostrarPerfil();
    }
} elseif ($_GET['action'] == "modificar_vehiculo"){
	$vehiculo->modificar_vehiculo($_POST);
} elseif ($_GET["action"] == "actualizar_vehiculo"){
	$vehiculo->actualizar_vehiculo($_POST);
} elseif ($_GET["action"] == "modificar_viaje_ocasional"){
	$viaje->modificar_viaje_ocasional($_POST);
} elseif ($_GET["action"] == "crear_viajeOcasional"){
	$viaje->publicarViajeOcasional($_POST);
} elseif ($_GET["action"] == "modificarViajeOcasional"){
	$viaje->modificarViajeOcasional($_POST);
} elseif ($_GET["action"] == "crear_viajePeriodico"){
	$viaje->publicarViajePeriodico($_POST);
} elseif ($_GET["action"] == "confirmar_eliminacion_en_cascada"){
	$vehiculo->confirmar_eliminacion_en_cascada($_POST);
} elseif ($_GET["action"] == "ver_publicacion_viaje"){
	$viaje->ver_publicacion_viaje($_POST);
} elseif ($_GET["action"] == "postularse"){
	$viaje->postularse($_POST);
} elseif ($_GET["action"] == "cancelar_postulacion"){
	$viaje->cancelar_postulacion($_POST);
} elseif ($_GET["action"] == "cancelar_postulacion_aceptada"){
	$viaje->cancelar_postulacion_aceptada($_POST);
} elseif ($_GET["action"] == "borrar_postulacion_aceptada"){
	$viaje->borrar_postulacion_aceptada($_POST);
} elseif ($_GET["action"] == "aceptarPostulado"){
	$viaje->aceptarPostulacionAViaje($_POST);
} elseif ($_GET["action"] == "confirmarEliminarViaje"){
	$viaje->confirmarEliminacionViaje($_POST);
}

