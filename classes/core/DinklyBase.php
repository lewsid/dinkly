<?php

use Symfony\Component\Yaml\Yaml;

class DinklyBase
{
	protected $module_header;

	protected $context;

	protected $view;

	protected $module;

	protected $parameters;

	//***************************************************************************** NONSTATIC FUNCTIONS

	//Init
	public function __construct($enable_cache = true)
	{
		//If the dinkly session doesn't exist yet, create it
		if(!isset($_SESSION['dinkly']) || !$enable_cache) $_SESSION['dinkly'] = array();

		//If the dinkly setting for the app root doesn't exist, create it
		if(!isset($_SESSION['dinkly']['app_root'])) { $_SESSION['dinkly']['app_root'] = $_SERVER['APPLICATION_ROOT']; }

		//If the current application root does not match what we have in session, reset the dinkly session
		//...this prevents issues when going from one Dinkly project to another in a local environment
		if($_SERVER['APPLICATION_ROOT'] != $_SESSION['dinkly']['app_root']) { $_SESSION['dinkly'] = array(); }
	}

	//Make sense of the friendly URLS and put us we we're supposed to be, with the parameters we expect.
	public function route($uri = null)
	{
		$parameters = array();

		if(stristr($uri, '?'))
		{
			$orig = $uri;
			$pos = strpos($uri, '?');
			$uri = substr($uri, 0, $pos);
			$query_string = substr($orig, $pos + 1);
			parse_str($query_string, $parameters);
		}

		$module = $view = null;

		$context = $this->getContext($uri);
		$context['parameters'] = array_merge($context['parameters'], $parameters);

		$_SESSION['dinkly']['current_app_name'] = $context['current_app_name'];

		$this->loadModule($context['current_app_name'], $context['module'], $context['view'], false, true, $context['parameters']);
	}

	public function getContext($uri = null)
	{
		if(!$this->context)
		{
			if(!$uri) { $uri = $_SERVER['REQUEST_URI']; }

			$current_app_name = $module = $view = null;
			$context = $parameters = array();

			$default_app_name = self::getDefaultApp(true);
			$config = self::getConfig();

			$uri_parts = array_filter(explode("/", $uri));

			//If the URL is empty, give it a slash so it can match in the config
			if($uri_parts == array()) { $uri_parts = array(1 => '/'); }

			//Figure out the current app, assume the default if we don't get one in the URL
			foreach($config as $app => $values)
			{
				if($app != 'global')
				{
					if(!isset($values['base_href']))
					{
						throw new Exception('base_href key/value pair missing from config.yml');
					}
					$base_href = str_replace('/', '', $values['base_href']);
					
					if(strlen($base_href) == 0) { $base_href = '/'; }
					
					if($uri_parts[1] == $base_href)
					{
						$context['current_app_name'] = $app;

						//kick the app off the uri and reindex
						array_shift($uri_parts); 

						break;
					}
				}
			}

			//No match, set default app
			if(!isset($context['current_app_name'])) { $context['current_app_name'] = $default_app_name; }

			//Reset indexes if needed
			$uri_parts = array_values($uri_parts);

			//Figure out the module and view
			if(sizeof($uri_parts) == 1) { $context['module'] = $uri_parts[0]; $context['view'] = 'default'; }
			else if(sizeof($uri_parts) == 2) { $context['module'] = $uri_parts[0]; $context['view'] = $uri_parts[1]; }
			else if(sizeof($uri_parts) > 2)
			{
				for($i = 0; $i < sizeof($uri_parts); $i++)
				{
					if($i == 0) { $context['module'] = $uri_parts[0]; }
					else if($i == 1) { $context['view'] = $uri_parts[1]; }
					else
					{
						if(isset($uri_parts[$i+1]))
						{
							$parameters[$uri_parts[$i]] = $uri_parts[$i+1];
							$i++;
						}
						else
						{
							$parameters[$uri_parts[$i]] = true;
						}
					}
				}    
			}

			if(!isset($context['module'])) { $context['module'] = Dinkly::getConfigValue('default_module', $context['current_app_name']); }
			if(!isset($context['view'])) { $context['view'] = 'default'; }

			$context['parameters'] = $parameters;

			$this->context = $context;
		}

		return $this->context;
	}

	//Dinkly's most badass function. Loads a desired module, and redirects if you ask it nicely.
	public function loadModule($app_name, $module_name = null, $view_name = 'default', $redirect = false, $draw_layout = true, $parameters = null)
	{
		//If the app_name is not passed, assume whichever is set as the default in config.yml
		if(!$app_name) $app_name = Dinkly::getDefaultApp(true);
		
		//Set the current app to match whatever was passed
		$_SESSION['dinkly']['current_app_name'] = $app_name;

		//If no view is passed, look for one called 'default'
		if(!$view_name) $view_name = 'default';

		//Determine if we are currently on this module/view or not
		if($redirect)
		{
			$base_href = Dinkly::getConfigValue('base_href', $app_name);
			if($base_href == '/') { $base_href = null; }
			$path = $base_href . '/' . $module_name . '/' . $view_name;

			//Deal with parameters
			if($parameters)
			{
				foreach($parameters as $key => $value)
				{
					$path .= '/' . $key . '/' . $value;
				}
			}

			header("Location: " . $path);
		}

		//Get module controller
		$camel_module_name = self::convertToCamelCase($module_name, true) . "Controller";
		$controller_file = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' . $module_name . '/' . $camel_module_name . '.php';

		//Save these on the object so they can be retrieved as needed in controllers or views
		$this->view = $view_name;
		$this->module = $module_name;
		$this->parameters = $parameters;

		//If the controller doesn't exist, load 404 error page if one is available, otherwise load default module
		if(!file_exists($controller_file))
		{
			$error_controller = $_SERVER['APPLICATION_ROOT'] . "/apps/" . $app_name . "/modules/error/ErrorController.php";
			
			if(file_exists($error_controller))
			{
				$camel_module_name = "ErrorController";
				$module_name = 'error';
				$controller_file = $error_controller;
				$view_name = '404';
			}
			else
			{
				//Check for base dinkly 404
				$error_controller = $_SERVER['APPLICATION_ROOT'] . "/apps/error/modules/error/ErrorController.php";
				
				if(file_exists($error_controller))
				{
					$app_name = 'error';
					$camel_module_name = "ErrorController";
					$module_name = 'error';
					$controller_file = $error_controller;
					$view_name = '404';
				}
				else
				{
					$camel_module_name = self::convertToCamelCase(self::getConfigValue('default_module', $app_name), true) . "Controller";
					$module_name = self::getConfigValue('default_module', $app_name);
					$controller_file = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' . self::getConfigValue('default_module', $app_name) . '/' . $camel_module_name . ".php";
				}
			}
		}

		//Instantiate controller object
		require_once $controller_file;
		$controller = new $camel_module_name();

		//Migrate current dinkly variables over to our new controller
		$vars = get_object_vars($this);
		foreach ($vars as $name => $value) { $controller->$name = $value; }

		//Get this view's function
		$view_controller_name = self::convertToCamelCase($view_name, true);
		$view_function = "load" . $view_controller_name;

		if(method_exists($controller, $view_function))
		{
			if($controller->$view_function($parameters))
			{
				if(!in_array($module_name, Dinkly::getValidModules($app_name)))
				{
					return false;
				}

				//Migrate the scope of the declared variables to be local to the view
				$vars = get_object_vars($controller);
				foreach ($vars as $name => $value)
					$$name = $value;

				//Get module view
				if(file_exists($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' . $module_name . '/views/' . $view_name . ".php"))
				{
					if($draw_layout)
					{
						if(file_exists($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' . $module_name . "/views/header.php"))
						{
							ob_start();
							include($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' . $module_name . "/views/header.php");
							$header = ob_get_contents();
							ob_end_clean();
							$this->setModuleHeader($header);
						}
						
						//Set the powered-by header if the version number is in the config
						if($version = self::getConfigValue('dinkly_version', 'global'))
						{
							header('X-Powered-By: DINKLY/' . $version);
						}

						include($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/layout/header.php');
					}

					require_once($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' . $module_name . '/views/' . $view_name . ".php");
					
					if($draw_layout)
					{
						require_once($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/layout/footer.php');
					}
				}
			}
		}
	}

	//Set the module header.
	public function setModuleHeader($header) { $this->module_header = $header; }

	//Returns the contents of the module header.
	public function getModuleHeader() { return $this->module_header; }

	//Return the current context's view
	public function getCurrentView()
	{
		if(!$this->view)
		{
			$context = $this->getContext();
			$this->view = $context['view'];
		}
		return $this->view;
	}

	//Return the current context's module
	public function getCurrentModule()
	{
		if(!$this->module)
		{
			$context = $this->getContext();
			$this->module = $context['module'];
		}
		return $this->module;
	}

	//Return parameters
	public function getParameters()
	{
		if(!$this->parameters)
		{
			$context = $this->getContext();
			$this->parameters = $context['parameters'];
		}
		return $this->parameters;
	}

	//***************************************************************************** STATIC FUNCTIONS

	//Return current application name
	public static function getCurrentAppName()
	{
		return $_SESSION['dinkly']['current_app_name'];
	}

	//If the configuration file hasn't been loaded, do so. Returns the configuration array.
	public static function getConfig()
	{
		$config = null;
		if(!isset($_SESSION['dinkly']['config']))
		{
			$config = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/config.yml");
			$_SESSION['dinkly']['config'] = $config;
		}
		else { $config = $_SESSION['dinkly']['config']; }
		
		return $config;
	}

	//Need a specific configuration value? This is for you.
	public static function getConfigValue($key, $app_name = null)
	{
		if(!$app_name) { $app_name = self::getDefaultApp(true); }

		$config = self::getConfig();

		if(isset($config[$app_name]))
		{
			if(isset($config[$app_name][$key]))
			{
				return $config[$app_name][$key];
			}
		}

		return false;
	}

	//A little helper function to convert camel case class names to their underscore equivalents.
	public static function convertFromCamelCase($str)
	{
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}
	
	//Convert underscored string into camel case. Used for class loading.
	public static function convertToCamelCase($str, $capitalise_first_char = false)
	{
		if($capitalise_first_char) $str[0] = strtoupper($str[0]);

		$func = create_function('$c', 'return strtoupper($c[1]);');
		
		return preg_replace_callback('/_([a-z])/', $func, $str);
	}

	//Returns an array of valid modules, that is, they actually exist.
	public static function getValidModules($app_name)
	{
		$valid_modules = null;

		if(!isset($_SESSION['dinkly']['valid_modules_' . $app_name]))
		{
			$valid_modules = array();
			if($handle = opendir($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/'))
			{ 
				/* loop through directory. */ 
				while (false !== ($dir = readdir($handle)))
				{ 
					if($dir != '.' && $dir != '..') { $valid_modules[] = $dir; }
				} 
				closedir($handle);
				
				$_SESSION['dinkly']['valid_modules_' . $app_name] = $valid_modules;
			}
		}
		else { $valid_modules = $_SESSION['dinkly']['valid_modules_' . $app_name]; }

		return $valid_modules;
	}

	//Return the default application configuration array.
	public static function getDefaultApp($return_name = false)
	{
		$config = self::getConfig();

		foreach($config as $app => $values)
		{
			if(isset($values['default_app']))
			{
				if($values['default_app'] == 'true')
				{
					if($return_name)
					{
						return $app;
					}
					return $config[$app];
				}
			}
		}
	}
}
