<?php
/**
 * CsrfProtectTokenizerController
 *
 *
 * @package    Dinkly
 * @subpackage PluginsCsrfProtectTokenizerController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class CsrfProtectTokenizerController extends CsrfProtectController
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
		if(!isset($_SESSION['dinkly']['csrf_token']))
		{
			$_SESSION['dinkly']['csrf_token'] = base64_encode(openssl_random_pseudo_bytes(32));
		}

		die($_SESSION['dinkly']['csrf_token']);

		return false;
	}
}
