<?php

die();

require_once('config/bootstrap.php');

if(AuthUser::authenticate('admin', 'password'))
{
	echo "\nAuthenticated!\n";
}
else { echo "\nNo sir, I don't like it.\n"; }