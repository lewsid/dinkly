<?php

session_start();

require_once('../config/bootstrap.php');

$view = null;
$app_name = Dinkly::getCurrentAppName();
if(isset($_GET['view'])) $view = $_GET['view'];
if(isset($_GET['module'])) $Dinkly->loadModule($app_name, $_GET['module'], $view);
else { $Dinkly->loadModule($app_name, Dinkly::getConfigValue('default_module')); }