<?php
/**
 * ApiRouterController
 *
 * A ready-to-go Restful API, useful for interaction with JS MVC frameworks
 *
 * @package    Dinkly
 * @subpackage AppsApiController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class ApiRouterController extends Dinkly 
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Handle any errors that occur internally with Server Side Data
	 *
	 * @param string $message: message you wish to display upon server side error
	 *
	 * 
	 */
	public function handleError($message)
	{
	    header('HTTP/1.1 500 Internal Server Error');
	    header('Content-Type: application/json');
	    die($message);
	}
	/**
	 * Handle JSON encoded data received from server
	 *
	 * @param JSON string $json: json encoded information from server
	 * 
	 */
	public function handleResponse($json)
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		echo $json;
	}
	/**
	 * Loads default data from server and handles accordingly based on type
	 *
	 * @return mixed data | bool: returns data when possible and false on failure
	 */
	public function loadDefault()
	{	
		$request = json_decode(file_get_contents('php://input'));

		$response = null;
		
		switch($_SERVER['REQUEST_METHOD'])
		{
			case 'GET':
				$response = 'GET received!';
				break;

			case 'POST':
				$response = 'POST received!';
				break;

			case 'PUT':
				$response = 'PUT received!';
				break;

			case 'DELETE':
				$response = 'DELETE received!';
				break;
		}

		$this->handleResponse($response);

		return false;
	}
}
