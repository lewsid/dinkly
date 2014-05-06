<?php
/**
 * DinklyBase
 *
 * 
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

use Symfony\Component\Yaml\Yaml;

class DinklyBase
{
	protected $module_header;

	protected $context;

	protected $view;

	protected $module;

	protected $parameters;

	//***************************************************************************** NONSTATIC FUNCTIONS


	/**
	 * Initialize dinkly session, Get app root and reset session root if not matching
	 *
	 * @param bool $enable_cache default true or enter false to flush session cache
	 * 
	 * 
	 */
	public function __construct($environment = 'dev', $empty_session = false)
	{
		//If the dinkly session doesn't exist yet, create it
		if(!isset($_SESSION['dinkly']) || $empty_session) { $_SESSION['dinkly'] = array(); }

		//If the current application root does not match what we have in session, reset the dinkly session
		//...this prevents issues when going from one Dinkly project to another in a local environment
		if(isset($_SESSION['dinkly']['app_root']))
		{
			if($_SERVER['APPLICATION_ROOT'] != $_SESSION['dinkly']['app_root']) { $_SESSION['dinkly'] = array(); }
		}

		//Set mode (prod or dev) (dev to display errors, disable config cache)
		if(isset($_SESSION['dinkly']['environment']))
		{
			//If the passed environment doesn't match the one in session, refresh the session
			if($_SESSION['dinkly']['environment'] != $environment) { $_SESSION['dinkly'] = array(); }
		}
		$_SESSION['dinkly']['environment'] = $environment;

		//Enable display of errors if we're in dev
		if($_SESSION['dinkly']['environment'] == 'dev') { ini_set('display_errors', 1); }

		//If the dinkly setting for the app root doesn't exist, create it
		if(!isset($_SESSION['dinkly']['app_root'])) { $_SESSION['dinkly']['app_root'] = $_SERVER['APPLICATION_ROOT']; }
	}

	/**
	 * Interpret friendly URLS and load app and module based on Context 
	 * as well as interpreting parameters where applicable
	 * @param string $uri default null to be parsed to get correct context
	 * @return Array of matching objects or false if not found
	 */
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

		$this->loadApp($context['current_app_name']);

		$this->loadModule($context['current_app_name'], $context['module'], $context['view'], false, true, $context['parameters']);
	}

	/**
	 * Fetch the current context of the application based on URL
	 * 
	 * @param string $uri default null to be parsed to get correct context
	 * @return Array $context containing current app, module and view
	 */
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
			foreach($config['apps'] as $app => $values)
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

	/**
	 * Load error Page when given a app that doesn't exist in context or 
	 * load default module if no error file
	 *
	 * @param string $$requested_app_name name of app we are trying to load
	 * @param string $requested_camel_module_name module we are looking for in camel case
	 * @param string $requested_view_name view we are looking for in camel case
	 * 
	 */
	public function loadError($requested_app_name, $requested_camel_module_name, $requested_view_name = null)
	{
		//Check for base dinkly 404
		$error_controller = $_SERVER['APPLICATION_ROOT'] . "/apps/error/modules/error/ErrorController.php";
		
		if(file_exists($error_controller))
		{
			return $this->loadModule('error', 'error', '404', true, true, $parameters = array('requested_app' => $requested_app_name, 'requested_module' => $requested_camel_module_name, 'requested_view' => $requested_view_name));
		}
	}

	/**
	 * Load application base in order to instantiate the app controller
	 * 
	 *
	 * @param string $app_name name of app we are trying to load
	 * 
	 * 
	 * @return bool true if app controller is locoated and instantiated, false if one can't be found
	 */
	public function loadApp($app_name)
	{
		$camel_app_controller_name = self::convertToCamelCase($app_name, true) . "Controller";
		$app_controller_file = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/' . $camel_app_controller_name . '.php';

		if(file_exists($app_controller_file))
		{
			//Instantiate controller object
			require_once $app_controller_file;
			$controller = new $camel_app_controller_name();

			return true;
		}

		return false;
	}

	/**
	 * Load desired module and redirect if necessary
	 * 
	 *
	 * @param string $app_name name of app we are trying to load
	 * @param string $module_name string of desired module to load
	 * @param string $view string if passed goes to specified view otherwise default
	 * @param bool $redirect default false, make true to redirect to different view
	 * @param bool $draw_layout default true to get module view
	 * @param array $parameters Array of parameters that can be used to populate views
	 *
	 * @return bool true if app loaded currectly else false and sent to default app
	 */
	public function loadModule($app_name, $module_name = null, $view_name = 'default', $redirect = false, $draw_layout = true, $parameters = null)
	{
		//If nested, prevent output from doubling
		if(headers_sent()) { return false; }

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
		
		//Load the app controller, if one exists
		$this->loadApp($app_name);

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
			$this->loadError($app_name, $camel_module_name, $view_name);
			return false;
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
			//If controller function returns false, don't draw layout wrapper
			if($controller->$view_function($parameters))
			{
				$draw_layout = true;
			}
			else { $draw_layout = false; }

			if(!in_array($module_name, Dinkly::getValidModules($app_name)))
			{
				throw new Exception('Module "' . $module_name . '" cannot be loaded.');
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
		else
		{
			$this->loadError($app_name, $camel_module_name, $view_name);
			return false;
		}

		return $draw_layout;
	}

	/**
	 * Set module header manually
	 *
	 * @param header $header String containing contents of header.php file
	 * 
	 */
	public function setModuleHeader($header) { $this->module_header = $header; }

	/**
	 * Get contents of module header
	 *
	 * 
	 * @return header contents of header.php file of a given module
	 */
	public function getModuleHeader() { return $this->module_header; }

	/**
	 * Get current contexts view
	 *
	 * 
	 * @return view of current context
	 */
	public function getCurrentView()
	{
		if(!$this->view)
		{
			$context = $this->getContext();
			$this->view = $context['view'];
		}
		return $this->view;
	}

	/**
	 * Get current contexts module
	 *
	 * 
	 * @return module of current context
	 */
	public function getCurrentModule()
	{
		if(!$this->module)
		{
			$context = $this->getContext();
			$this->module = $context['module'];
		}
		return $this->module;
	}

	/**
	 * Get current environment name
	 *
	 * 
	 * @return string name of the current environment
	 */
	public static function getCurrentEnvironment()
	{
		if(isset($_SESSION['dinkly']['environment']))
		{
			return $_SESSION['dinkly']['environment'];
		}
	}

	/**
	 * Get current contexts parameters
	 *
	 * 
	 * @return parameters of current context
	 */
	public function getParameters()
	{
		if(!$this->parameters)
		{
			$context = $this->getContext();
			$this->parameters = $context['parameters'];
		}
		return $this->parameters;
	}

	/**
	 * Get current application's name from dinkly session
	 *
	 * 
	 * @return string name of application
	 */
	public static function getCurrentAppName()
	{
		if(isset($_SESSION['dinkly']['current_app_name']))
		{
			return $_SESSION['dinkly']['current_app_name'];
		}
	}

	/**
	 * Validate that all the minimum configuration settings exist
	 * @param Array $config: array containing configuration settings to be validated
	 * 
	 * @throws Missing default module if no default module is set in app config
	 * @throws Missing base href if no base href field is found in app config
	 * @throws Missing default app if no default app field is found in app config
	 *
	 * @return bool: true if the configuration is valid, false otherwise
	 */
	public static function validateConfig($config)
	{
		if(sizeof($config['apps']) < 1)
		{
			throw new Exception('Missing apps setting in config.yml');
			return false;
		}

		$has_default_app = false;
		foreach($config['apps'] as $app_name => $app_config)
		{
			if(isset($app_config['default_app'])) { $has_default_app = true; }

			if(!isset($app_config['default_module']))
			{
				throw new Exception('Missing default_module setting for module \'' . $app_name . '\' in config.yml');
			}

			if(!isset($app_config['base_href']))
			{
				throw new Exception('Missing base_href setting for module \'' . $app_name . '\' in config.yml');
			}
		}

		if(!$has_default_app)
		{
			throw new Exception('Missing default_app setting in config.yml');
		}

		return true;
	}

	/**
	 * If the configuration file hasn't been loaded, do so. Returns the configuration array.
	 *
	 * 
	 * @return Array containting current configuration
	 */
	public static function getConfig()
	{
		$env = 'dev';
		if(isset($_SESSION['dinkly']['environment'])) { $env = $_SESSION['dinkly']['environment']; }

		$config = null;
		if(!isset($_SESSION['dinkly']['config']) || $env == 'dev')
		{
			$raw_config = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/config.yml");
			$config = $raw_config['global'];

			if(isset($raw_config[$env]))
			{
				if(isset($raw_config[$env]['apps']))
				{
					foreach($raw_config[$env]['apps'] as $app_name => $app_config)
					{
						foreach($app_config as $config_name => $config_value)
						{
							$config['apps'][$app_name][$config_name] = $config_value;
						}
					}
				}
				if(isset($raw_config[$env]['databases']))
				{
					foreach($raw_config[$env]['databases'] as $schema => $db_config)
					{
						foreach($db_config as $config_name => $config_value)
						{
							$config['databases'][$schema][$config_name] = $config_value;
						}
					}
				}
			}

			if(self::validateConfig($config)) { $_SESSION['dinkly']['config'] = $config; }
			
		}
		else { $config = $_SESSION['dinkly']['config']; }

		return $config;
	}

	/**
	 * Get a specific property value of the configuration
	 * @param string $key string to index into config array
	 * @param string $app_name specify app to get config value from
	 *
	 * @return mixed value of config spec
	 */
	public static function getConfigValue($key, $app_name = null)
	{
		if(!$app_name) { $app_name = self::getDefaultApp(true); }

		$config = self::getConfig();

		if(isset($config['settings'][$key])) { return $config['settings'][$key]; } 
		else if(isset($config['apps'][$app_name]))
		{
			if(isset($config['apps'][$app_name][$key]))
			{
				return $config['apps'][$app_name][$key];
			}
		}

		return false;
	}

	/**
	 * Convert a string from camel case for class name conversion
	 * @param string $str String in camel case to be converted
	 * 
	 * @return string in lower case spaced with underscores
	 */
	public static function convertFromCamelCase($str)
	{
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}

	/**
	 * Convert underscored string to camel case for class loading
	 * @param string $str String underscored to be converted
	 * @param bool $capitalise_first choose whether first letter should be in caps 
	 *
	 * @return string in normal camel case or all upper camel case
	 */
	public static function convertToCamelCase($str, $capitalise_first_char = false)
	{
		if($capitalise_first_char) $str[0] = strtoupper($str[0]);

		$func = create_function('$c', 'return strtoupper($c[1]);');
		
		return preg_replace_callback('/_([a-z])/', $func, $str);
	}

	/**
	 * Get existing modules
	 * @param string $app_name String name of app from which you want modules
	 *
	 * @return Array of valid modules
	 */
	public static function getValidModules($app_name)
	{
		$valid_modules = null;

		if(!isset($_SESSION['dinkly']['valid_modules_' . $app_name]) || self::getCurrentEnvironment() == 'dev')
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
	
	/**
	 * Get default application from config array
	 *
	 * @param bool $return_name make true to return app name not config array
	 *
	 * @return Array of application config of default app
	 */
	public static function getDefaultApp($return_name = false)
	{
		$config = self::getConfig();

		foreach($config['apps'] as $app => $values)
		{
			if(isset($values['default_app']))
			{
				if($values['default_app'] == 'true')
				{
					if($return_name)
					{
						return $app;
					}
					return $config['apps'][$app];
				}
			}
		}
	}
}
