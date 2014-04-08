<?php 

$_SERVER['APPLICATION_ROOT'] = dirname(__FILE__) . '/../';
require_once $_SERVER['APPLICATION_ROOT'] . 'vendor/autoload.php';
require_once $_SERVER['APPLICATION_ROOT'] . '/classes/core/autoload.php';

$Dinkly = new Dinkly('dev');