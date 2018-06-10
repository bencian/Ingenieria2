<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
session_set_cookie_params(360000000,"/");
session_start();

require_once('controller/AppController.php');
require_once('controller/AppControllerViajes.php');
require_once('controller/AppControllerUsuario.php');

require_once('model/PDORepository.php');
require_once('model/AppModel.php');

require_once('view/TwigView.php');
require_once('view/Home.php');

