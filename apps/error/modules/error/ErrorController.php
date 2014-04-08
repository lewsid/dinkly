<?php
/**
 * ErrorController
 *
 * 
 *
 * @package    Dinkly
 * @subpackage AppsErrorErrorController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class ErrorController extends Dinkly 
{
	/**
	 * Handle any URL errors when page is not found
	 * 
	 * @return bool: always returns true on page not found error
	 * 
	 */
	public function load404()
	{
		header("HTTP/1.0 404 Not Found");
		
		return true;
	}
}
