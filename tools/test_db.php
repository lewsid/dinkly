<?php

/* Use this to test your database connection, as configured in classes/dbconfig.php */

die();

require_once('config/bootstrap.php');

if(DBConfig::testDB()) { echo "\nsuccessfully connected to database!\n"; }
else { echo "\nUnable to connect to database!\n"; }
