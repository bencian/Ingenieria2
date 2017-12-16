<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

require_once('controller/AppController.php');
require_once('model/PDORepository.php');
require_once('model/AppModel.php');
require_once('view/TwigView.php');
require_once('view/Home.php');

if(!isset($_GET["action"])){
	AppController::getInstance()->login();
}elseif ($_GET["action"] == "validarLog"){
	AppController::getInstance()->validarLogin($_POST);
}elseif ($_GET["action"] == "formPN"){
	AppController::getInstance()-> cargarFormPN(); 
}elseif ($_GET["action"] == "validarFormPN"){
	AppController::getInstance()-> validarPN($_POST);
}elseif ($_GET["action"] == "listarTodos"){
	AppController::getInstance()-> listarPedidos();
}


