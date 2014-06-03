<?php
/**
 * AdminController
 *
 * 
 *
 * @package    Dinkly
 * @subpackage AppsAdminController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class AdminController extends Dinkly
{
	/**
	 * Default Constructor
	 *
	 */
	public function __construct()
	{
		//Let's make this accessible across the admin for display of all dates
		$this->date_format = null;

		//We use this for the profile modal
		$this->logged_user = null;

		if(DinklyUser::isLoggedIn())
		{
			$this->logged_user = new DinklyUser();
			$this->logged_user->init(DinklyUser::getAuthSessionValue('logged_id'));
			$this->date_format = $this->logged_user->getDateFormat() . ' ' . Dinkly::getConfigValue('time_format');
			return false;
		}

		return true;
	}
}