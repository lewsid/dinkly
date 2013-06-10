<?php

/* Use this to test your database connection, as configured in classes/DinklyDataConfig.php */

require_once('config/bootstrap.php');

if(DinklyDataConnector::testDB()) { echo "\nSuccessfully connected to database!\n"; }
else { echo "\nUnable to connect to database!\n"; }
