<?php

session_start();

require_once('../config/bootstrap.php');

if(isset($_GET['nocache'])) { session_destroy(); header("Location: /"); }

$Dinkly->route($_SERVER['REQUEST_URI']);