<?php

die();

require_once('config/bootstrap.php');

$user = new AuthUser();
$user->setUsername('admin');
$user->setPassword('password');
$user->setCreatedAt(date("Y-m-d G:i:s"));
$user->setUpdatedAt(date("Y-m-d G:i:s"));
$user->setLoginCount(0);
if($user->save()) { echo "\nUser Created!\n"; }
else { echo "\nUnable to create user, check database settings (and that the table exists!)\n"; }