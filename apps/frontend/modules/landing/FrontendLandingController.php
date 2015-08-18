<?php
/**
 * FrontendLandingController
 *
 *
 * @package    Dinkly
 * @subpackage AppsFrontendLandingController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class FrontendLandingController extends FrontendController
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
