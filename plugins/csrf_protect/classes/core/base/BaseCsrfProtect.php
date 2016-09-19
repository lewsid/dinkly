<?php

class BaseCsrfProtect extends Dinkly
{
	public static function enforce()
	{
		//Verify all POST and GET traffic for valid csrf tokens
		if($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			if(!(isset($_POST['csrf_token'])))
			{
				throw new Exception('CSRF Error: No POST token detected');
			}
			elseif(isset($_POST['csrf_token']) && $_POST['csrf_token'] != $_SESSION['dinkly']['csrf_token'])
			{
				throw new Exception('CSRF Error: Invalid POST token detected');
			}
		}

		return true;
	}
}