<?php

require_once('config/bootstrap.php');

$sql = false;
$options = getopt("insert");
if(isset($options['i'])) { $sql = true; }
ModelBuilder::buildAll($sql);