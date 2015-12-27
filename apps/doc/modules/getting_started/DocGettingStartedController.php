<?php
/**
 * GettingStartedController
 *
 *
 * @package    Dinkly
 * @subpackage AppsDocGettingStartedController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class DocGettingStartedController extends DocController
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

	public function loadDefault()
	{
		return true;
	}

	public function loadInstallation()
	{
		return true;
	}

	public function loadRequirements()
	{
		return true;
	}

	public function loadConfiguration()
	{
		return true;
	}

	public function loadInitializingTheAdmin()
	{
		return true;
	}

	public function loadUnderstandingTheArchitecture()
	{
		return true;
	}

	public function loadPlugins()
	{
		return true;
	}
}
