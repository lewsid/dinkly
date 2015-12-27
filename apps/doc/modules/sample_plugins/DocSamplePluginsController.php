<?php
/**
 * SamplePluginsController
 *
 *
 * @package    Dinkly
 * @subpackage AppsDocSamplePluginsController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class DocSamplePluginsController extends DocController
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

	public function loadImageResizer()
	{
		return true;
	}
}
