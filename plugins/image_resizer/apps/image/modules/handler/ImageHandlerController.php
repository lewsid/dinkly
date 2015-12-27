<?php
/**
 * HandlerController
 *
 *
 * @package    Dinkly
 * @subpackage AppsImageHandlerController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class ImageHandlerController extends ImageController
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

	/* <img src="/image/handler/image/cropped/false/w/500/h/315/file_name/my_image.png" alt=""> */
	public function loadImage($parameters)
	{
		$filename = $parameters['file_name'];
		if(isset($parameters['w'])){ 
			ImageResizer::serveImage($filename, $parameters['w'],$parameters['h'],$parameters['cropped'], $filename);
		}
		else
		{
			$path_to_file = $_SERVER['APPLICATION_ROOT'] . Dinkly::getConfigValue('files_directory', 'image') . '/' . $filename;

			if(file_exists($path_to_file))
			{
				header('Content-Description: File Transfer');
				header("Content-type: " . ImageResizer::getMimeType($path_to_file));
				header('Content-Disposition: attachment; filename=' . str_replace(",", " ", $filename));
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($path_to_file));
				readfile($path_to_file);
				die();
			}
		}
	}
}
