<?php

ini_set('memory_limit','2000M');

class ImageResizer
{
	public static function getMimeType($file)
	{
		// our list of mime types
		$mime_types = array(
			"pdf"=>"application/pdf"
			,"exe"=>"application/octet-stream"
			,"zip"=>"application/zip"
			,"docx"=>"application/msword"
			,"doc"=>"application/msword"
			,"xls"=>"application/vnd.ms-excel"
			,"ppt"=>"application/vnd.ms-powerpoint"
			,"gif"=>"image/gif"
			,"png"=>"image/png"
			,"jpeg"=>"image/jpg"
			,"jpg"=>"image/jpg"
			,"mp3"=>"audio/mpeg"
			,"wav"=>"audio/x-wav"
			,"mpeg"=>"video/mpeg"
			,"mpg"=>"video/mpeg"
			,"mpe"=>"video/mpeg"
			,"mov"=>"video/quicktime"
			,"avi"=>"video/x-msvideo"
			,"3gp"=>"video/3gpp"
			,"css"=>"text/css"
			,"jsc"=>"application/javascript"
			,"js"=>"application/javascript"
			,"php"=>"text/html"
			,"htm"=>"text/html"
			,"html"=>"text/html"
		);

		$tmp = explode('.',$file);
		$extension = strtolower(end($tmp));

		return $mime_types[$extension];
	}

	//try to find an image matching uri parameters if not found create, return image
	public static function serveImage($src, $width, $height, $cropped = null, $original_name)
	{
		if($cropped)
		{
			$filename = "cropped-";
		}
		else
		{
			$filename = "";
		}
		$filename .= $width."-".$height."-".$src;

		if(Dinkly::getConfigValue('files_directory', 'image'))
			$files_directory = $_SERVER['APPLICATION_ROOT'] . Dinkly::getConfigValue('files_directory', 'image');
		else
			$files_directory = $_SERVER['APPLICATION_ROOT'] . 'uploads'; 

		//if dir uploads doesn't exist create it
		if(!is_dir($files_directory))
			mkdir($files_directory);

		$file = $files_directory . '/resized/' .$filename;

		//if dir resized doesn't exist create it
		if(!is_dir($files_directory . '/resized'))
			mkdir($files_directory . '/resized');

		if(file_exists ( $file ))
		{
			header('Content-Description: File Transfer');
			header("Content-type: " . ImageResizer::getMimeType($file));
			header('Content-Disposition: attachment; filename=' . str_replace(","," ", $original_name));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);

			die();
		}
		else
		{
			$src = $files_directory . '/' .$src;

			if($cropped == "true")
			{
				self::croppedResize($src, $files_directory . '/resized/'.$filename, $width, $height);
			}
			else
			{
				self::resizeByWidth($src, $files_directory . '/resized/'.$filename, $width);
			}

			header('Content-Description: File Transfer');
			header("Content-type: " . ImageResizer::getMimeType($file));
			header('Content-Disposition: attachment; filename=' . str_replace(","," ", $original_name));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			die();
		}
	}

	public static function resizeByWidth($src, $dest, $desired_width, $format = null)
	{
		/* read the source image */
		$source_image = null;

		// If no format, try to get it from filename
		if(!$format)
		{
			$ext_parts = explode(".", $src);
			$ext = end($ext_parts);
			$ext_to_format = array(
				'jpg' => 'image/jpg',
				'jpeg' => 'image/jpg',
				'png' => 'image/png'
			);
			if(array_key_exists($ext, $ext_to_format))
			{
				$format = $ext_to_format[$ext];
			}
			// jpg is our fallback
			else
			{
				$format = "image/jpg";
			}
		}
		if($format == 'image/jpeg' || $format == 'image/jpg')
		{
			$source_image = imagecreatefromjpeg($src);
		}
		else if($format == 'image/png')
		{
			$source_image = imagecreatefrompng($src);
		}

		// If we couldn't form the file, we have an invalid image file
		if(!$source_image)
		{
			return false;
		}

		$width = imagesx($source_image);
		$height = imagesy($source_image);
		
		/* find the "desired height" of this thumbnail, relative to the desired width  */
		$desired_height = floor($height * ($desired_width / $width));
		
		/* create a new, "virtual" image */
		$virtual_image = imagecreatetruecolor($desired_width, $desired_height);

		imageAlphaBlending($virtual_image, false);
		imageSaveAlpha($virtual_image, true);
		
		/* copy source image at a resized size */
		imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
		
		/* create the physical thumbnail image to its destination */
		if($format == 'image/jpeg' || $format == 'image/jpg')
		{
			imagejpeg($virtual_image, $dest);
		}
		else if($format == 'image/png')
		{
			imagepng($virtual_image, $dest);
		}
	}

	public static function croppedResize($src, $dest, $desired_width, $desired_height, $format = null)
	{
		$source_image = null;

		// If no format, try to get it from filename
		if(!$format)
		{
			$ext_parts = explode(".", $src);
			$ext = end($ext_parts);
			$ext_to_format = array(
				'jpg' => 'image/jpg',
				'jpeg' => 'image/jpg',
				'png' => 'image/png'
			);
			if(array_key_exists($ext, $ext_to_format))
			{
				$format = $ext_to_format[$ext];
			}
			// jpg is our fallback
			else
			{
				$format = "image/jpg";
			}
		}

		if($format == 'image/jpeg' || $format == 'image/jpg')
		{
			$source_image = imagecreatefromjpeg($src);
		}
		else if($format == 'image/png')
		{
			$source_image = imagecreatefrompng($src);
		}

		// If we couldn't form the file, we have an invalid image file
		if(!$source_image)
		{
			return false;
		}

		$original_width = imagesx($source_image);
		$original_height = imagesy($source_image);

		$width = round($desired_width);
		$height = round($desired_height);

		// create a new, 'virtual' image
		$virtual_image = imagecreatetruecolor($width, $height);
		
		// Preserves transparency between images
		imageAlphaBlending($virtual_image, false);
		imageSaveAlpha($virtual_image, true);
		
		$destAR = $width / $height;
		if($width > 0 && $height > 0)
		{
			// We can't divide by zero theres something wrong.
			$srcAR = $original_width / $original_height;
		
			// Destination narrower than the source
			if($destAR < $srcAR)
			{
				$srcY = 0;
				$srcHeight = $original_height;
				
				$srcWidth = round( $original_height * $destAR );
				$srcX = round( ($original_width - $srcWidth) / 2 );
			}
			else // Destination shorter than the source
			{
				$srcX = 0;
				$srcWidth = $original_width;
				
				$srcHeight = round( $original_width / $destAR );
				$srcY = round( ($original_height - $srcHeight) / 2 );
			}
			
			imagecopyresampled($virtual_image, $source_image, 0,0, $srcX, $srcY, $width, $height, $srcWidth, $srcHeight);
		}

		// Create the physical thumbnail image to its destination */
		if($format == 'image/jpeg' || $format == 'image/jpg')
		{
			imagejpeg($virtual_image, $dest);
		}
		else if($format == 'image/png')
		{
			imagepng($virtual_image, $dest);
		}
	}
}