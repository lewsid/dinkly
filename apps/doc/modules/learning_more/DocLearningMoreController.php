<?php
/**
 * LearningMoreController
 *
 *
 * @package    Dinkly
 * @subpackage AppsDocLearningMoreController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class DocLearningMoreController extends DocController
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

	public function loadConfiguration()
	{
		return true;
	}
}
