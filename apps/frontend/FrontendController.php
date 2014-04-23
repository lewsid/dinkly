<?php
/**
 * FrontendController
 * 
 *
 * @package    Dinkly
 * @subpackage AppsFrontendController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class FrontendController extends Dinkly
{
/**
 * Default Constructor
 * 
 * @return bool: always returns true on successful construction of view
 * 
 */
	public function __construct()
	{
		return $this->loadModule('admin', 'home', 'default', false);
	}
}
