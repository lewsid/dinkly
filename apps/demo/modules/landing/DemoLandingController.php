<?php
/**
 * DemoLandingController
 *
 *
 * @package    Dinkly
 * @subpackage AppsDemoLandingController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class DemoLandingController extends DemoController
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Load default view
	 *
	 * @return bool: always returns true on successful construction of view
	 *
	 */
	public function loadDefault()
	{
		return true;
	}
}
