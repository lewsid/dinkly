<?php
$options = getopt("f::t::");

$runAllUnitTests = 'tools/unit_tests';

$unitTestArgs = empty($options['f']) ? $runAllUnitTests : $options['f'];

if(!empty($options['t'])) {
	if(!empty($options['f'])) {
		$unitTestArgs = " --filter {$options['t']} " . $unitTestArgs;
	} else {
		echo "Need file path to run a single test\n\n";
		return false;
	}
}

system("php vendor/phpunit/phpunit/phpunit --bootstrap config/bootstrap.php {$unitTestArgs}");
