<?php

use Symfony\Component\Yaml\Yaml;

class Dinkly
{
  private $module_header;

  public function __construct($enable_cache = true)
  {
    if(!isset($_SESSION['dinkly']) || !$enable_cache || isset($_GET['nocache'])) $_SESSION['dinkly'] = array();
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
      $app_name = self::getCurrentAppName();

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

  protected static function getValidModules()
  {
    $valid_modules = null;

    if(!isset($_SESSION['dinkly']['valid_modules']))
    {
      $valid_modules = array();
      if($handle = opendir($_SERVER['APPLICATION_ROOT'] . '/apps/' . self::getCurrentAppName() . '/modules/'))
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

  protected function isNewContext($app, $module_name = null, $view_name = '')
  {
    $set_path = false;

    $module_param = null; $view_param = 'default';
    if(isset($_GET['module'])) { $module_param = $_GET['module']; }
    if(isset($_GET['view'])) { $view_param = $_GET['view']; }

    if($module_param != $module_name || $view_param != $view_name)
    {
      $set_path = Dinkly::getConfigValue('base_href') . $module_name . '/';  
      if($view_name != 'default') { $set_path .= $view_name . '/'; }
    }

    return $set_path;
  }

  public static function getCurrentAppName()
  {
    $config = self::getConfig();

    //Figure out what app we're in and if there's a matching route
    $url_parts = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $app_route = '/'.$url_parts[1];

    $default_app = null;
    foreach($config as $app => $values)
    {
      if(isset($values['default_app']))
      {
          if($values['default_app'] == 'true')
          {
            $default_app = $app;
          }
      }
      if($app_route == $values['base_href'])
      {
        return $app;
      }
    }

    //No match found, return default
    return $default_app;
  }

  public function loadModule($app_name, $module_name = null, $view_name = 'default', $redirect = false, $draw_layout = true)
  {    
    if(!$view_name) $view_name = 'default';

    //Determine if we are currently on this module/view or not
    if(($new_path = $this->isNewContext($app_name, $module_name, $view_name)) && $redirect)
    {
      header("Location: " . $new_path);
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

    if($controller->$view_function())
    {
      if(!in_array($module_name, Dinkly::getValidModules())) { return false; }
      
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