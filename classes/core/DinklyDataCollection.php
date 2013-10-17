<?php

/****************************************************************************************************************

	CHILD CLASS EXAMPLE:

		class UserCollection extends DinklyDataCollection
		{
			public static function getAll()
			{
				$peer_object = new User();
				if(self::fetchDB()->query($peer_object->getSelectQuery(), true)) { return self::handleResults($peer_object); }
			}
		}

	CHILD CLASS USAGE EXAMPLE:

		$users = UserCollection::getAll();

***************************************************************************************************************/

abstract class DinklyDataCollection extends DinklyDataModel
{

	public static function getAll()
	{
		$peer_class = preg_replace('/Collection$/', '', get_called_class());
		if(class_exists($peer_class)) {
			$peer_object = new $peer_class();
			return self::getCollection($peer_object, $peer_object->getSelectQuery());
		}
	}


	protected static function getCollection($peer_object, $query)
	{
		$db = self::fetchDB();
		$results = $db->query($query)->fetchAll();

		if($results != array() && $results != NULL)
		{
			$arrObject = array();
			$i = 0;
			foreach($results as $result)
			{
				$class_name = get_class($peer_object);
				$tempObject = new $class_name($db);
				$tempObject->hydrate($result, true);

				$arrObject[$i] = $tempObject;

				$i++;
			}
			return $arrObject;
		}
	}
}