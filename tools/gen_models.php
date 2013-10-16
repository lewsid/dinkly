<?php

require_once('config/bootstrap.php');

$sql = $target_connection = $schema = null;
$options = getopt("s:i");
if(isset($options['i'])) { $sql = true; }
if(isset($options['s'])) { $schema = $options['s']; }
DinklyBuilder::buildAllModels($schema, $sql);
