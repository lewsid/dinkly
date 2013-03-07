<?php

session_start();

require_once('../config/bootstrap.php');

if(isset($_GET['nocache'])) unset($_SESSION['dinkly']);
else { $Dinkly->route($_SERVER['REQUEST_URI']); }