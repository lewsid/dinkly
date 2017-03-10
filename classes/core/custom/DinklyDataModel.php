<?php 
/**
 * DinklyDataModel
 *
 * 
 *
 * @package    Dinkly
 * @subpackage CoreClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */


abstract class DinklyDataModel extends BaseDinklyDataModel
{
	//Put your overrides here...
	public function getPrimaryKey()
	{
		//$sql = "SHOW KEYS FROM " . $this->getDBTable() . " WHERE Key_name = 'PRIMARY'";
		$sql = "SELECT KU.table_name as TABLENAME,column_name as PRIMARYKEYCOLUMN
					FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC
					INNER JOIN
						INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KU
							ON TC.CONSTRAINT_TYPE = 'PRIMARY KEY' AND
								TC.CONSTRAINT_NAME = KU.CONSTRAINT_NAME AND 
								KU.table_name='" . $this->getDBTable() . "'
					ORDER BY KU.TABLE_NAME, KU.ORDINAL_POSITION";
		$result = $this->db->query($sql)->fetch();

		//return $result['Column_name'];
		return $result['PRIMARYKEYCOLUMN'];
	}
}