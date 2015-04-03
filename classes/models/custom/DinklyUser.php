<?php
/**
 * DinklyUser
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class DinklyUser extends BaseDinklyUser
{
	protected $groups = array();

	protected function convertDate($format = null, $datetime)
	{
		if($format)
		{
			if($datetime == '0000-00-00 00:00:00' || !$datetime)
			{
				return null;
			}

			return date($format, strtotime($datetime));
		}

		return $datetime;
	}

	public function getDateFormat()
	{
		if(!$this->DateFormat)
		{
			return 'm/d/y';
		}
		else { return $this->DateFormat; }
	}

	public function getCreatedAt($format = null)
	{
		return $this->convertDate($format, $this->CreatedAt);
	}

	public function getUpdatedAt($format = null)
	{
		return $this->convertDate($format, $this->UpdatedAt);
	}

	public function getLastLoginAt($format = null)
	{
		return $this->convertDate($format, $this->LastLoginAt);
	}

	public function getGroups()
	{
		if($this->groups == array())
		{
			$this->groups = DinklyUserGroupCollection::getGroupsByUser($this->getId());
		}

		return $this->groups;
	}

	public function delete()
	{
		$group_ids = array();
		$groups = $this->getGroups();

		if($groups != array())
		{
			foreach($groups as $group)
			{
				$this->removeFromGroup($group->getId());
			}
		}

		parent::delete();
	}

	public function removeFromGroup($group_id)
	{
		$group = new DinklyGroup();
		$group->init($group_id);

		//If the group isn't new, that means it exists, which is a good thing
		if(!$group->isNew())
		{
			$group_join = new DinklyUserGroup();
			$group_join->initWithUserAndGroup($this->getId(), $group_id);
			
			if(!$group_join->isNew())
			{
				$group_join->delete();

				return true;
			}
		}

		return false;
	}

	public function addToGroups($group_ids)
	{
		if($group_ids != array())
		{
			foreach($group_ids as $id)
			{
				$group = new DinklyGroup();
				$group->init($id);

				//If the group isn't new, that means it exists, which is a good thing
				if(!$group->isNew())
				{
					//Make sure this join record doesn't already exist first
					$group_join = new DinklyUserGroup();
					$group_join->initWithUserAndGroup($this->getId(), $id);
					
					if($group_join->isNew())
					{
						$group_join->setDinklyUserId($this->getId());
						$group_join->setDinklyGroupId($id);
						$group_join->save();
					}
				}
			}

			return true;
		}

		return false;
	}

	public static function isMemberOf($group_abbreviation)
	{
		if(self::getLoggedGroups() != array())
		{
			foreach(self::getLoggedGroups() as $group)
			{
				if($group['abbreviation'] == $group_abbreviation)
				{
					return true;
				}
			}
		}
	}

	public static function hasPermission($permission_name)
	{
		if(self::getLoggedPermissions() != array())
		{
			foreach(self::getLoggedPermissions() as $permission)
			{
				if($permission['name'] == $permission_name)
				{
					return true;
				}
			}
		}
	}

	/**
	 * Set password of admin user
	 *
	 * @param string $password: password you wish to set for admin user
	 *
	 * 
	 */
	public function setPassword($password)
	{
		$this->Password = crypt($password);
		$this->regDirty['password'] = true;
	}

	/**
	 * Check Dinkly session to see if admin user is logged in
	 *
	 * 
	 * @return bool true if logged in, false if not
	 */
	public static function isLoggedIn()
	{
		if(self::getAuthSessionValue('logged_in')) { return true; }
		return false;
	}

	public static function getAuthSessionValue($key)
	{
		if(!isset($_SESSION['dinkly']['auth'])) { $_SESSION['dinkly']['auth'] = array(); }

		if(!isset($_SESSION['dinkly']['auth'][Dinkly::getCurrentAppName()]))
		{
			$_SESSION['dinkly']['auth'][Dinkly::getCurrentAppName()] = array();
		}
		if(isset($_SESSION['dinkly']['auth'][Dinkly::getCurrentAppName()][$key])) { return $_SESSION['dinkly']['auth'][Dinkly::getCurrentAppName()][$key]; }

		return false;
	}

	public static function setAuthSessionValue($key, $value, $app = null)
	{
		if(!$app)
		{
			$app = Dinkly::getCurrentAppName();
		}
		
		if(!isset($_SESSION['dinkly']['auth'][$app]))
		{
			$_SESSION['dinkly']['auth'][$app] = array();
		}
		$_SESSION['dinkly']['auth'][$app][$key] = $value;
	}

	/**
	 * Retrieve all objects matching array of passed property/value pairs
	 *
	 * @param bool $val: set true to log in admin user
	 * @param string $username: username of admin user to set sessions
	 * 
	 */
	public static function setLoggedIn($is_logged_in, $user_id, $username, $groups = array(), $app = null)
	{
		if(!$app) $app = Dinkly::getCurrentAppName();

		self::setAuthSessionValue('logged_in', $is_logged_in, $app);
		self::setAuthSessionValue('logged_username', $username, $app);
		self::setAuthSessionValue('logged_id', $user_id, $app);

		if($groups != array())
		{
			$logged_groups = array();
			$logged_permissions = array();

			foreach($groups as $group)
			{
				$logged_groups[] = array('id' => $group->getId(), 'abbreviation' => $group->getAbbreviation());

				$permissions = $group->getPermissions();

				if($permissions != array())
				{
					foreach($permissions as $permission)
					{
						$logged_permissions[] = array('id' => $permission->getId(), 'name' => $permission->getName());
					}
				}
			}

			self::setAuthSessionValue('logged_groups', $logged_groups, $app);
			self::setAuthSessionValue('logged_permissions', $logged_permissions, $app);
		}
	}

	/**
	 * Check Dinkly session to get username of admin that is logged in
	 *
	 * 
	 * @return mixed string | bool: string username if logged in, else false
	 */
	public static function getLoggedUsername()
	{
		return self::getAuthSessionValue('logged_username');
	}

	public static function getLoggedGroups()
	{
		return self::getAuthSessionValue('logged_groups');
	}

	public static function getLoggedId()
	{
		return self::getAuthSessionValue('logged_id');
	}

	/**
	 * Clear Dinkly session variables to log out user
	 *
	 * 
	 * 
	 */
	public static function logout()
	{	
		$_SESSION['dinkly']['auth'][Dinkly::getCurrentAppName()] = null;
	}
	
	/**
	 * Verify with database the user credentials are correct and log in if so
	 * 
	 *
	 * @param string $username: input username of user attempting to log in
	 * @param string $input_password: input password of user attempting to log in
	 * 
	 * @return bool: true if correct credentials and logged on, false otherwise
	 */
	public static function authenticate($username, $input_password)
	{
		$dbo = self::fetchDB();

		$sql = "select * from dinkly_user where username=".$dbo->quote($username);
		$result = $dbo->query($sql)->fetchAll();

		//We found a match for the username      
		if($result != array())
		{
			$user = new DinklyUser();
			$user->init($result[0]['id']);
			$hashed_password = $result[0]['password'];

			if(crypt($input_password, $hashed_password) == $hashed_password)
			{
				$count = $user->getLoginCount() + 1;

				$user->setLastLoginAt(date('Y-m-d G:i:s'));
				$user->setLoginCount($count);
				$user->save();

				self::setLoggedIn(true, $result[0]['id'], $result[0]['username'], $user->getGroups());

				return true;
			}
		}

		return false;
	}
}

