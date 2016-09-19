<?php
/**
 * HomeController
 *
 *
 * @package    Dinkly
 * @subpackage AppsDocHomeController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class DocHomeController extends DocController
{
	/**
	 * Constructor
	 *
	 * @return void
	 *
	 */
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

	public function loadIntroduction()
	{
		return true;
	}
}
