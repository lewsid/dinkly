<?php

session_start();

require_once('../config/bootstrap.php');

if(isset($_GET['nocache'])) { session_destroy(); header("Location: /"); }

if(isset($_GET['showsession'])) { echo '<pre>'; print_r($_SESSION); die(); }

$Dinkly->route($_SERVER['REQUEST_URI']);