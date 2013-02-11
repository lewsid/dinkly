<?php

require_once('../config/bootstrap.php');

$config = DBConfig::getDBCreds();
$db = new DBHelper($config);
$db->Update("create database " . $config['DB_NAME']);
exec('mysql -u ' . $config['DB_USER'] . ' -p' . $config['DB_PASS'] . ' ' . $config['DB_NAME'] 
	. ' < dinkly_auth_user.sql');