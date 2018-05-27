<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
session_set_cookie_params(3600,"/");
session_start();

require_once('controller/AppController.php');
require_once('model/PDORepository.php');
require_once('model/AppModel.php');
require_once('view/TwigView.php');
require_once('view/Home.php');

//SI se agrega un controller o un model debe "registrarse" con el required once como se muestra ahi arriba (los templates no).
if(!isset($_GET["action"])){
	AppController::getInstance()->index();
} elseif ($_GET["action"] == "login"){
	AppController::getInstance()->login($_POST);
} elseif ($_GET["action"] == "registrarse"){
	AppController::getInstance()->registrarse($_POST);
} elseif ($_GET["action"] == "registrar_vehiculo"){
	AppController::getInstance()->registrar_vehiculo();
} elseif ($_GET["action"] == "crear_usuario"){
	AppController::getInstance()->crear_usuario($_POST);
} elseif ($_GET["action"] == "nueva_Sesion"){
	AppController::getInstance()->validar_Inicio_Sesion($_POST);
} elseif ($_GET["action"] == "cerrar_sesion"){
	AppController::getInstance()->cerrarSesion();
} elseif ($_GET["action"] == "mostrar_perfil"){
	AppController::getInstance()->mostrarPerfil();
} elseif ($_GET["action"] == "crear_vehiculo"){
	AppController::getInstance()->crear_vehiculo($_POST);
} elseif ($_GET["action"] == "modificar_perfil"){
	AppController::getInstance()->modificar_perfil();
} elseif ($_GET["action"] == "actualizar_perfil"){
	AppController::getInstance()->actualizar_perfil($_POST);
} elseif ($_GET["action"] == "buscando"){
	AppController::getInstance()->buscador($_POST);
} /*elseif ($_GET["action"] == "lista_vehiculos"){
	AppController::getInstance()->listar_vehiculos($_GET);
}*/