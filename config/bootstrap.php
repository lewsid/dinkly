<?php 

date_default_timezone_set("America/New_York");

$_SERVER['APPLICATION_ROOT'] = dirname(__FILE__) . '/../';

require_once $_SERVER['APPLICATION_ROOT'] . 'vendor/autoload.php';
require_once $_SERVER['APPLICATION_ROOT'] . 'config/autoload.php';

$Dinkly = new Dinkly('dev');