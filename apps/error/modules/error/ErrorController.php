<?php

class ErrorController extends Dinkly 
{
	public function load404()
	{
		header("HTTP/1.0 404 Not Found");
		
		return true;
	}
}
