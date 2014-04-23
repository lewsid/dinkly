<?php
/**
 * HomeController
 *
 * 
 *
 * @package    Dinkly
 * @subpackage AppsAdminHomeHomeController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class HomeController extends AdminController 
{
	/**
	 * Loads default admin home page using headers
	 * 
	 * @return bool: always returns true on successful construction of default admin home view
	 * 
	 */
	public function loadDefault($parameters = array())
	{
		$this->invalid_login = false;

		if(isset($parameters['invalid_login'])) $this->invalid_login = true;

		return true;
	}
}