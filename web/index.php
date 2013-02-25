<?php

ini_set('display_errors', 1);

require_once('../config/bootstrap.php');

$view = null;
if(isset($_GET['view'])) $view = $_GET['view'];
if(isset($_GET['module'])) $Dinkly->loadModule($_GET['module'], $view);
else $Dinkly->loadModule('home', 'default', true, false); //default module
