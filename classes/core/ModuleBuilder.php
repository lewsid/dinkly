<?php

class ModuleBuilder extends Dinkly
{
	public static function buildModule($module_name)
	{
		$module_folder = $_SERVER['APPLICATION_ROOT'] . "modules/" . $module_name;
		if(!is_dir($module_folder))
		{
			if(mkdir($module_folder))
			{
				mkdir($module_folder . "/views");
				
				$fp = fopen($module_folder . "/views/default.php", 'w+');
				fclose($fp);

				$fp = fopen($module_folder . "/" . Dinkly::convertToCamelCase($module_name, true) . "Controller.php", 'w+');
				fwrite($fp, '<?php' . PHP_EOL . PHP_EOL);
				fwrite($fp, 'class ' . Dinkly::convertToCamelCase($module_name, true) . 'Controller extends Dinkly ' . PHP_EOL . '{' . PHP_EOL);
				fwrite($fp, "\tpublic function loadDefault()" . PHP_EOL . "\t{" . PHP_EOL);
				fwrite($fp, "\t\treturn true;" . PHP_EOL . "\t}" . PHP_EOL . "}" . PHP_EOL);
				fclose($fp);
			}
			else
			{
				echo "\nError: Unable to create module directory.";
			}
		}
		else 
		{
			echo "\nError: That module already exists.\n\n";
			return false;
		}
	}
}