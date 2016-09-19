<?php
/**
 * ProfileController
 *
 *
 * @package    Dinkly
 * @subpackage AppsAdminProfileController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */

class AdminProfileController extends AdminController
{
	public function __construct()
	{
		parent::__construct();

		$this->errors = array();
	}

	//http://stackoverflow.com/a/7022536/53079
	public function formatOffset($offset)
	{
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);

        if ($hour == 0 AND $minutes == 0) {
            $sign = ' ';
        }
        return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) .':'. str_pad($minutes,2, '0');

	}

	public function validateUserPost($post_array)
	{
		if($post_array['email'] == "")
		{
			$this->errors[] = "Email is a required field";
		}
		//If the passed username doesn't match the existing one, update
		else if($post_array['email'] != $this->user->getUsername())
		{
			//Check the username/email for uniqueness
			if(!DinklyUserCollection::isUniqueUsername($post_array['email']))
			{
				$this->errors[] = "Email address already in use, please try another.";
			}

			//Make sure it's also a valid email address
			if(!filter_var($post_array['email'], FILTER_VALIDATE_EMAIL))
			{
			    $this->errors[] = "Invalid email. Not a valid email address.";
			}

			$this->user->setUsername(strip_tags($post_array['email']));

			//If we're editing the current user, we should update the session'd username
			if($this->user->getId() == DinklyUser::getAuthSessionValue('logged_id'))
			{
				DinklyUser::setAuthSessionValue('logged_username', $this->user->getUsername());
			}
		}

		//If the password isn't blank
		if($post_array['password'] != "" && $post_array['confirm-password'] != "")
		{
			$has_error = false;

			//Make sure both match
			if($post_array['password'] != $post_array['confirm-password'])
			{
				$has_error = true;
				$this->errors[] = "Passwords do not match.";
			}

			//Check for length
			if(strlen($post_array['password']) < 8)
			{
				$has_error = true;
				$this->errors[] = "Password must be at least 8 characters in length.";
			}

			//If the password is valid, update
			if(!$has_error) { $this->user->setPassword($post_array['password']); }
		}
		else if($_POST['user-id'] == "" && $_POST['password'] == "")
		{
			$this->errors[] = "Password is a required field";
		}

		if($post_array['first-name'] == "")
		{
			$this->errors[] = "First Name is a required field";
		}

		if($post_array['last-name'] == "")
		{
			$this->errors[] = "Last Name is a required field";
		}

		//If the first name isn't empty and doesn't match the existing one, update
		if($post_array['first-name'] != "" && $post_array['first-name'] != $this->user->getFirstName())
		{
			$this->user->setFirstName(strip_tags($post_array['first-name']));
		}

		//If the last name isn't empty and doesn't match the exiting one, update
		if($post_array['last-name'] != "" && $post_array['last-name'] != $this->user->getLastName())
		{
			$this->user->setLastName(strip_tags($post_array['last-name']));
		}

		//If the title isn't empty and does't match the existing one, update
		if(($post_array['title'] != "" && $post_array['title'] != $this->user->getTitle()) || $post_array['title'] == "")
		{
			$this->user->setTitle(strip_tags($post_array['title']));
		}

		//Nothing to validate here really
		if($this->user->getTimeZone() == '') { $this->user->setTimeZone('America/New_York'); }
		else { $this->user->setTimeZone(strip_tags($post_array['time-zone'])); }
	}

	/**
	 * Load default view
	 *
	 * @return bool: always returns true on successful construction of view
	 *
	 */
	public function loadDefault()
	{
		$this->user = $this->logged_user;

		//Handle save
		if(isset($_POST['user-id']))
		{
			$this->user->init($_POST['user-id']);

			//Make sure the submitted user matches the one logged in
			if($_POST['user-id'] == DinklyUser::getLoggedId())
			{
				$this->validateUserPost($_POST);

				if($_POST['date-format'] == 'MM/DD/YY')
				{
					$this->user->setDateFormat('m/d/y');
				}
				else if($_POST['date-format'] == 'DD/MM/YY')
				{
					$this->user->setDateFormat('d/m/y');
				}

				if($_POST['time-format'] == '12')
				{
					$this->user->setTimeFormat('g:i a');
				}
				else if($_POST['time-format'] == '24')
				{
					$this->user->setTimeFormat('H:i');
				}

				//If we have no errors, save the user
				if($this->errors == array())
				{
					$this->user->save();

					$this->logged_user = $this->user;

					DinklyFlash::set('good_user_message', 'Profile Updated');				
				}
			}
		}

		//Timezone dropdown (http://stackoverflow.com/a/7022536/53079)
		$utc = new DateTimeZone('UTC');
		$dt = new DateTime('now', $utc);

		$this->select_options = null;	
		$timezone_identifiers = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, 'US');
		foreach($timezone_identifiers as $tz)
		{
		    $current_tz = new DateTimeZone($tz);
		    $offset =  $current_tz->getOffset($dt);
		    $transition =  $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());
		    $abbr = $transition[0]['abbr'];

		    $selected = null;
		    if($this->user->getTimeZone() == $tz) $selected = 'selected="selected"';

		    $this->select_options .= '<option ' . $selected . ' value="' .$tz. '">' 
		    	. str_replace('_', ' ', $tz) . ' [' .$abbr. ' '. DinklyUser::formatOffset($offset). ']</option>';
		}

		return true;
	}
}
