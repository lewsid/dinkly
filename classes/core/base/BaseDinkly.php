<?php
/**
 * BaseDinkly
 *
 * 
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

use Symfony\Component\Yaml\Yaml;

class BaseDinkly
{
	protected $module_header;

	protected $module_footer;

	protected $context;

	protected $view;

	protected $module;

	protected $parameters;

	//***************************************************************************** NONSTATIC FUNCTIONS


	/**
	 * Initialize dinkly session, Get app root and reset session root if not matching
	 *
	 * @param bool $enable_cache default true or enter false to flush session cache
	 * @param bool $empty_session Wipes the current Dinkly session in favor of a new one, handy when you
	 *			   switch the environment once you've already intantiated Dinkly using another.
	 * @param bool $dev_mode same as naming your environment 'dev' but without that environment being
	 *			   named 'dev' (errors will be displayed and the session caching is disabled) 
	 * 
	 */
	public function __construct($environment = null, $empty_session = false, $dev_mode = false)
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
			if($environment)
			{
				if($_SESSION['dinkly']['environment'] != $environment) { $_SESSION['dinkly'] = array(); }
			}
		}
		else
		{
			if(!$environment) { $environment = 'dev'; }
			$_SESSION['dinkly']['environment'] = $environment;
		}

		//Enable display of errors if we're in dev
		$_SESSION['dinkly']['dev_mode'] = false;
		if(isset($_SESSION['dinkly']['environment']))
		{
			if($_SESSION['dinkly']['environment'] == 'dev' || $dev_mode == true)
			{
				ini_set('display_errors', 1);
				$_SESSION['dinkly']['dev_mode'] = true;
			}
		}

		//If the dinkly setting for the app root doesn't exist, create it
		if(!isset($_SESSION['dinkly']['app_root'])) { $_SESSION['dinkly']['app_root'] = $_SERVER['APPLICATION_ROOT']; }
	}

	public static function translate($source_string, $locale = null)
	{
		if(!$locale)
		{
			if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			{
				$locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
			}
			else { $locale = 'en_US'; }
		}

		$languages = self::getConfigValue('languages');

		if($languages != array())
		{
			foreach($languages as $language_group => $language_codes)
			{
				if(file_exists($_SERVER['APPLICATION_ROOT'] . "config/i18n/" . $language_group . ".yml"))
				{
					foreach($language_codes as $code)
					{
						if(!isset($_SESSION['dinkly']['languages'])) { $_SESSION['dinkly']['languages'] = array(); }

						$translations = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/i18n/" . $language_group . ".yml");
						$_SESSION['dinkly']['languages'][$code] = $translations;
					}
				}
			}
		}
		
		if(isset($_SESSION['dinkly']['languages'][$locale]['translations'][$source_string]))
		{
			return $_SESSION['dinkly']['languages'][$locale]['translations'][$source_string];
		}

		//Default to original string
		return $source_string;
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

		$this->updateContextHistory($this->context);

		return $this->context;
	}

	/**
	 * Load previous module
	 *
	 * @param string $depth How deep into the context stack you want to go. Default is 1, which returns the module 1
     *                      previous to the current.	                        
     * @param bool $redirect default false, make true to redirect to different view
	 * @param bool $draw_layout default true to get module view
	 * @param array $parameters Array of parameters that can be used to populate views (defaults to last module's parameters)
	 *
	 * @return bool true if app loaded currectly else false and sent to default app
	 */
	public function loadPreviousModule($depth = 1, $redirect = false, $draw_layout = true, $parameters = array())
	{
		//function loadModule($app_name, $module_name = null, $view_name = 'default', $redirect = false, $draw_layout = true, $parameters = null)
		$context = $this->getPreviousContext($depth);

		if($context['parameters'] != array())
		{
			$parameters = $context['parameters'];
		}

		return $this->loadModule($context['current_app_name'], $context['module'], $context['view'], $redirect, $draw_layout, $parameters);
	}

	/**
	 * Return previous context
	 *
	 * @param string $depth How deep into the stack you want to go. Default is 1, which returns the context 1
     *                      previous to the current.	                        
	 *
	 * @return array of previous context (will be empty if no previous context can be returned)
	 */
	public function getPreviousContext($depth = 1)
	{
		$context_history = $this->getContextHistory();

		$offset = $depth + 1;
		$previous_position = abs(sizeof($context_history) - $offset);

		if(isset($context_history[$previous_position]))
		{
			return $context_history[$previous_position];			
		}
		
		return array();
	}

	/**
	 * Update the context history stack
	 *
	 * @param string $current_context current context array
	 * @param string $stack_height the max size of the history array to store in session, default is 10
	 * 
	 * @return boolean true on success
	 */
	public function updateContextHistory($current_context, $stack_height = 10)
	{
		if(!isset($_SESSION['dinkly']['context_history']))
		{
			$_SESSION['dinkly']['context_history'] = array();
		}

		if($this->context)
		{
			array_push($_SESSION['dinkly']['context_history'], $current_context);
		}

		if(sizeof($_SESSION['dinkly']['context_history']) > $stack_height)
		{
			array_shift($_SESSION['dinkly']['context_history']);
		}

		return true;
	}

	/**
	 * Return the context history stack
	 * 
	 * @return array context history
	 */
	public function getContextHistory()
	{
		if(!isset($_SESSION['dinkly']['context_history']))
		{
			$_SESSION['dinkly']['context_history'] = array();
		}

		return $_SESSION['dinkly']['context_history'];
	}

	/**
	 * Load error Page when given a app that doesn't exist in context or 
	 * load default module if no error file
	 *
	 * @param string $requested_app_name name of app we are trying to load
	 * @param string $requested_camel_module_name module we are looking for in camel case
	 * @param string $requested_view_name view we are looking for in camel case
	 * 
	 */
	public function loadError($requested_app_name, $requested_camel_module_name, $requested_view_name = null)
	{
		//Check for base dinkly 404
		$error_controller = $_SERVER['APPLICATION_ROOT'] . "/apps/error/modules/http/ErrorHttpController.php";
		
		if(file_exists($error_controller))
		{
			return $this->loadModule('error', 'http', '404', false, true, $parameters = array('requested_app' => $requested_app_name, 'requested_module' => $requested_camel_module_name, 'requested_view' => $requested_view_name));
		}
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

		//Validate passed app
		if(!in_array($app_name, Dinkly::getValidApps($app_name)))
		{
			throw new Exception('App "' . $app_name . '" cannot be loaded.');
			return false;
		}

		if(!Dinkly::isAppEnabled($app_name))
		{
			$message = "The requested app (" . $app_name . ") is currently disabled.";
			error_log($message);

			if(self::isDevMode())
			{
				echo $message;	
			}
			return false;
		}
		
		//Set the current app to match whatever was passed
		$_SESSION['dinkly']['current_app_name'] = $app_name;

		//If no view is passed, look for one called 'default'
		if(!$view_name) $view_name = 'default';

		//Redirect page if true
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
			die();
		}

		//Switch out paths later based on whether this is a plugin or not
		$is_plugin = false;
		if(Dinkly::getConfigValue('is_plugin', $app_name))
		{
			$is_plugin = true;
		}
		
		//Load the app controller, if one exists
		$camel_app_controller_name = self::convertToCamelCase($app_name, true) . "Controller";

		if($is_plugin)
		{
			$plugin_name = Dinkly::getConfigValue('plugin_name', $app_name);
			$app_controller_file = $_SERVER['APPLICATION_ROOT'] . '/plugins/' . $plugin_name . '/apps/' 
				. $app_name . '/' . $camel_app_controller_name . '.php';
		}
		else
		{
			$app_controller_file = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name 
				. '/' . $camel_app_controller_name . '.php';
		}

		$has_app_controller = false;
		if(file_exists($app_controller_file))
		{
			//Instantiate controller object
			require_once $app_controller_file;

			$has_app_controller = true;
		}

		//Get module controller
		$camel_module_name = self::convertToCamelCase($app_name, true) . self::convertToCamelCase($module_name, true) . "Controller";

		if($is_plugin)
		{
			$controller_file = $_SERVER['APPLICATION_ROOT'] . '/plugins/' . $plugin_name . '/apps/' 
				. $app_name . '/modules/' . $module_name . '/' . $camel_module_name . '.php';
		}
		else
		{
			$controller_file = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name 
				. '/modules/' . $module_name . '/' . $camel_module_name . '.php';
		}

		//Save these on the object so they can be retrieved as needed in controllers or views
		$this->view = $view_name;
		$this->module = $module_name;
		$this->parameters = $parameters;

		//If the controller doesn't exist, load 404 error page
		if(!file_exists($controller_file))
		{
			//If there's an app controller, we instantiate that, in case it has overrides
			if($has_app_controller)
			{
				$app_controller = new $camel_app_controller_name();
				$this->loadError($app_name, $camel_module_name, $view_name);
				return false;
			}
			else
			{
				$this->loadError($app_name, $camel_module_name, $view_name);
				return false;
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
				$$name = Dinkly::migrateVariable($value);

			//Draw headers
			if($draw_layout)
			{
				if($is_plugin)
				{
					$base_module_header_path = $_SERVER['APPLICATION_ROOT'] . '/plugins/' . $plugin_name . '/apps/' 
						. $app_name . '/modules/' . $module_name . '/views/header';
				}
				else
				{
					$base_module_header_path = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name 
						. '/modules/' . $module_name . "/views/header";
				}

				if(file_exists($base_module_header_path . '.php') || file_exists($base_module_header_path . '.md'))
				{
					ob_start();

					if(file_exists($base_module_header_path . '.php'))
					{
						include($base_module_header_path . '.php');
					}
					else if(file_exists($base_module_header_path . '.md'))
					{
						include($base_module_header_path . '.md');
					}
					
					$header = ob_get_contents();
					ob_end_clean();
					$this->setModuleHeader($header);
				}
				
				//Set the powered-by header if the version number is in the config
				if($version = self::getConfigValue('dinkly_version', 'global'))
				{
					header('X-Powered-By: DINKLY/' . $version);
				}

				if($is_plugin)
				{
					$app_header_path = $_SERVER['APPLICATION_ROOT'] . '/plugins/' . $plugin_name 
						. '/apps/' . $app_name . '/layout/header.php';
				}
				else
				{
					$app_header_path = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/layout/header.php';
				}

				if(file_exists($app_header_path))
				{
					include($app_header_path);
				}
			}

			//Draw view
			if($is_plugin)
			{
				$base_view_path = $_SERVER['APPLICATION_ROOT'] . '/plugins/' . $plugin_name 
						. '/apps/' . $app_name . '/modules/' . $module_name . '/views/' . $view_name; 
			}
			else
			{
				$base_view_path = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' 
					. $module_name . '/views/' . $view_name; 
			}
			
			if(file_exists($base_view_path . '.php'))
			{
				include($base_view_path . '.php');
			}
			else if(file_exists($base_view_path . '.md'))
			{
				$markdown_template = file_get_contents($base_view_path . '.md');
				$Parsedown = new Parsedown();
				echo $Parsedown->text($markdown_template);
			}
			
			//Draw footer
			if($draw_layout)
			{
				if($is_plugin)
				{
					$base_module_footer_path = $_SERVER['APPLICATION_ROOT'] . '/plugins/' . $plugin_name 
						. '/apps/' . $app_name . '/modules/' . $module_name . "/views/footer";
				}
				else
				{
					$base_module_footer_path = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' 
						. $module_name . "/views/footer";
				}

				if(file_exists($base_module_footer_path . '.php') || file_exists($base_module_footer_path . '.md'))
				{
					ob_start();

					if(file_exists($base_module_footer_path . '.php'))
					{
						include($base_module_footer_path . '.php');
					}
					else if(file_exists($base_module_footer_path . '.md'))
					{
						include($base_module_footer_path . '.md');
					}
					
					$footer = ob_get_contents();
					ob_end_clean();
					$this->setModuleFooter($footer);
				}

				if($is_plugin)
				{
					$app_footer_path = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/layout/footer.php';
				}
				else
				{
					$app_footer_path = $_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/layout/footer.php';
				}

				if(file_exists($app_footer_path))
				{
					include($app_footer_path);
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
	 * Pass variable through here to allow an override function where 
	 * sanitization could occur
	 * 
	 * @return value of migrated variable
	 */
	public static function migrateVariable($value) { return $value; }

	/**
	 * Set module header manually
	 *
	 * @param header $header String containing contents of header.php file
	 * 
	 */
	public function setModuleHeader($header) { $this->module_header = $header; }

	/**
	 * Set module footer manually
	 *
	 * @param footer $footer String containing contents of footer.php file
	 * 
	 */
	public function setModuleFooter($footer) { $this->module_footer = $footer; }

	/**
	 * Get contents of module header
	 *
	 * 
	 * @return header contents of header.php file of a given module
	 */
	public function getModuleHeader() { return $this->module_header; }

	/**
	 * Get contents of footer header
	 *
	 * 
	 * @return footer contents of footer.php file of a given module
	 */
	public function getModuleFooter() { return $this->module_footer; }

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

	public static function isDevMode()
	{
		if(isset($_SESSION['dinkly']['dev_mode']))
		{
			return $_SESSION['dinkly']['dev_mode'];
		}
	}

	/**
	 * Determine if a parameter has been set or not
	 * 
	 * @return boolean true if parameter exists
	 */
	public function hasParameter($parameter_name)
	{
		$parameters = $this->getParameters();

		return isset($parameters[$parameter_name]);
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
		if(!isset($_SESSION['dinkly']['config']) || self::isDevMode())
		{
			$raw_config = Yaml::parse($_SERVER['APPLICATION_ROOT'] . "config/config.yml");
			$config = $raw_config['global'];

			//Load Global Plugins Config
			if(isset($raw_config['global']['plugins']))
			{
				foreach($raw_config['global']['plugins'] as $plugin_name => $plugin_config)
				{
					foreach($plugin_config['apps'] as $app_name => $app_config)
					{	
						foreach($app_config as $config_name => $config_value)
						{
							$config['apps'][$app_name][$config_name] = $config_value;
							$config['apps'][$app_name]['plugin_name'] = $plugin_name;
						}
					}
				}
			}

			if($env != 'global')
			{
				if(isset($raw_config[$env]))
				{
					//Override/Merge Apps Config
					if(isset($raw_config[$env]['settings']))
					{
						foreach($raw_config[$env]['settings'] as $key => $value)
						{
							$config['settings'][$key] = $value;
						}
					}

					//Override/Merge Apps Config
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

					//Override/Merge Plugins Config
					if(isset($raw_config[$env]['plugins']))
					{
						foreach($raw_config[$env]['plugins'] as $plugin_name => $plugin_config)
						{
							foreach($plugin_config as $app_name => $app_config)
							{	
								foreach($app_config as $config_name => $config_value)
								{
									$config['apps'][$app_name][$config_name] = $config_value;
									$config['apps'][$app_name]['plugin_name'] = $plugin_name;
								}
							}
						}
					}

					//Override/Merge Databases Config
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
	 * Get existing apps
	 *
	 * @return Array of valid apps
	 */
	public static function getValidApps()
	{
		$valid_apps = null;

		if(!isset($_SESSION['dinkly']['valid_apps']))
		{ 
			$_SESSION['dinkly']['valid_apps'] = array();
			$valid_apps = array();

			//Load standard apps
			if($handle = opendir($_SERVER['APPLICATION_ROOT'] . '/apps/'))
			{ 
				/* loop through directory. */ 
				while (false !== ($dir = readdir($handle)))
				{ 
					if($dir != '.' && $dir != '..') { $valid_apps[] = $dir; }
				} 
				closedir($handle);
			}

			//Load plugins as valid apps
			if($handle = opendir($_SERVER['APPLICATION_ROOT'] . '/plugins/'))
			{
				//loop through plugins directory
				while (false !== ($dir = readdir($handle)))
				{ 
					if($dir != '.' && $dir != '..')
					{
						if($plugin_handle = opendir($_SERVER['APPLICATION_ROOT'] . '/plugins/' . $dir . '/apps/'))
						{
							//loop through plugin apps directory
							while (false !== ($plugin_dir = readdir($plugin_handle)))
							{ 
								if($plugin_dir != '.' && $plugin_dir != '..')
								{ 
									if(!in_array($plugin_dir, $valid_apps))
									{
										$valid_apps[] = $plugin_dir;
									}
								}
							} 
							closedir($plugin_handle);
						}
					}
				} 
				closedir($handle);
			}

			$_SESSION['dinkly']['valid_apps'] = $valid_apps;
		}

		return $_SESSION['dinkly']['valid_apps'];
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

		if(!isset($_SESSION['dinkly']['valid_modules'])) { $_SESSION['dinkly']['valid_modules'] = array(); }

		if(!isset($_SESSION['dinkly']['valid_modules'][$app_name]) || self::isDevMode())
		{
			$valid_modules = array();
			if(is_dir($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/'))
			{
				if($handle = opendir($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/'))
				{ 
					//loop through modules directory
					while (false !== ($dir = readdir($handle)))
					{ 
						if($dir != '.' && $dir != '..') { $valid_modules[] = $dir; }
					} 
					closedir($handle);
					
					$_SESSION['dinkly']['valid_modules'][$app_name] = $valid_modules;
				}
			}
			
			//Load plugins as valid modules
			if($handle = opendir($_SERVER['APPLICATION_ROOT'] . '/plugins/'))
			{
				//loop through plugins directory
				while (false !== ($dir = readdir($handle)))
				{ 
					if($dir != '.' && $dir != '..')
					{
						if($plugin_handle = opendir($_SERVER['APPLICATION_ROOT'] . '/plugins/' . $dir . '/apps/'))
						{
							//loop through plugin apps directory
							while (false !== ($plugin_dir = readdir($plugin_handle)))
							{ 
								if($plugin_dir != '.' && $plugin_dir != '..')
								{ 
									$plugin_modules_dir = $_SERVER['APPLICATION_ROOT'] . '/plugins/' . $dir . '/apps/' . $plugin_dir . '/modules/';
									if(is_dir($plugin_modules_dir))
									{
										if($h = opendir($plugin_modules_dir))
										{ 
											//loop through modules directory
											while (false !== ($d = readdir($h)))
											{ 
												if($d != '.' && $d != '..') { $valid_modules[] = $d; }
											} 
											closedir($h);
											
											$_SESSION['dinkly']['valid_modules'][$app_name] = $valid_modules;
										}
									}
								}
							} 
							closedir($plugin_handle);
						}
					}
				} 
				closedir($handle);
			}
		}
		else { $valid_modules = $_SESSION['dinkly']['valid_modules'][$app_name]; }

		return $valid_modules;
	}

	/**
	 * Determine if the passed app is enabled or not
	 *
	 * @param string $app_name
	 *
	 * @return bool
	 */
	public static function isAppEnabled($app_name)
	{
		$config = self::getConfig();

		if(isset($config['apps'][$app_name]['enabled']))
		{
			if($config['apps'][$app_name]['enabled'] == false) { return false; }
		}

		return true;
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
