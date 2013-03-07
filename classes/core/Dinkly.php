<?php

use Symfony\Component\Yaml\Yaml;

class Dinkly
{
  private $module_header;

  public function __construct($enable_cache = true)
  {
    if(!isset($_SESSION['dinkly']) || !$enable_cache) $_SESSION['dinkly'] = array();
  }

  public static function getCurrentAppName()
  {
    return $_SESSION['dinkly']['current_app_name'];
  }

  public function route($uri)
  {
    $current_app_name = $using_default = $module = $view = null;
    $parameters = array();

    $default_app_name = self::getDefaultApp(true);
    $config = self::getConfig();

    $uri_parts = array_filter(explode("/", $uri));

    //Figure out the current app, assume the default if we don't get one in the URL
    foreach($uri_parts as $part)
    {
      foreach($config as $app => $values)
      {
        if($part == $app)
        {
          $current_app_name = $app;

          //kick the app off the uri and reindex
          array_shift($uri_parts); 
        }
      }
    }

    //No match, set default app
    if(!$current_app_name)
    {
      $current_app_name = $default_app_name;
      $using_default = true;
    }

    $_SESSION['dinkly']['current_app_name'] = $current_app_name;
    
    //Reset indexes if needed
    $uri_parts = array_values($uri_parts);

    //Figure out the module and view
    if(sizeof($uri_parts) == 1) { $module = $uri_parts[0]; $view = 'default'; }
    else if(sizeof($uri_parts) == 2) { $module = $uri_parts[0]; $view = $uri_parts[1]; }
    else if(sizeof($uri_parts) > 2)
    {
      for($i = 0; $i < sizeof($uri_parts); $i++)
      {
        if($i == 0) { $module = $uri_parts[0]; }
        else if($i == 1) { $view = $uri_parts[1]; }
        else
        {
          if(isset($uri_parts[$i+1]))
          {
            $parameters[$uri_parts[$i]] = $uri_parts[$i+1];
            $i++;
          }
          else
          {
            $parameters[$part] = true;
          }
        }
      }    
    }

    if(!$module) { $module = Dinkly::getConfigValue('default_module', $current_app_name); }
    if(!$view) { $view = 'default'; }

    $this->loadModule($current_app_name, $module, $view, false, true, $parameters);
  }

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

  public static function getConfigValue($key, $app_name = null)
  {
    if(!$app_name)
      $app_name = self::getDefaultApp(true);

    $config = self::getConfig();
    return $config[$app_name][$key];
  }

  public static function convertFromCamelCase($str)
  {
    $str[0] = strtolower($str[0]);
    $func = create_function('$c', 'return "_" . strtolower($c[1]);');
    
    return preg_replace_callback('/([A-Z])/', $func, $str);
  }
  
  public static function convertToCamelCase($str, $capitalise_first_char = false)
  {
    if($capitalise_first_char) $str[0] = strtoupper($str[0]);

    $func = create_function('$c', 'return strtoupper($c[1]);');
    
    return preg_replace_callback('/_([a-z])/', $func, $str);
  }

  protected static function getValidModules($app_name)
  {
    $valid_modules = null;

    if(!isset($_SESSION['dinkly']['valid_modules']))
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
        
        $_SESSION['dinkly']['valid_modules'] = $valid_modules;
      }
    }
    else { $valid_modules = $_SESSION['dinkly']['valid_modules']; }

    return $valid_modules;
  }

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

  public function loadModule($app_name, $module_name = null, $view_name = 'default', $redirect = false, $draw_layout = true, $parameters = null)
  {
    if(!$app_name) $app_name = Dinkly::getDefaultApp(true);

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
    if(!file_exists($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' . $module_name . '/' . $camel_module_name . ".php"))
    {
      throw new Exception("No matching controller found");
    }

    $controller = new $camel_module_name;

    //Get this view's function
    $view_controller_name = self::convertToCamelCase($view_name, true);
    $view_function = "load" . $view_controller_name;

    if($controller->$view_function($parameters))
    {
      if(!in_array($module_name, Dinkly::getValidModules($app_name))) { return false; }
      
      //Migrate the scope of the declared variables to be local to the view
      $vars = get_object_vars($controller);
      foreach($vars as $name => $value) 
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
          require_once($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/layout/header.php');
        }
        require_once($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/modules/' . $module_name . '/views/' . $view_name . ".php");
        if($draw_layout) { require_once($_SERVER['APPLICATION_ROOT'] . '/apps/' . $app_name . '/layout/footer.php'); }
      }
    }
  }

  public function setModuleHeader($header) { $this->module_header = $header; }

  public function getModuleHeader() { return $this->module_header; }
}