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

	protected function insert()
	{
		if(!$this->db) { throw New Exception("Unable to perform insert without a database object"); }

		//Automatically set created_at, if it exists on this object
		if(property_exists($this, 'CreatedAt'))
		{
			if($this->getCreatedAt() == '' || $this->getCreatedAt() == '00-00-0000 00:00:00' 
				|| $this->getCreatedAt() == null)
			{
				$this->setCreatedAt(date('Y-m-d H:i:s'));
			}
		}

		//Automatically set updated_at, if it exists on this object
		if(property_exists($this, 'UpdatedAt'))
		{
			if($this->getUpdatedAt() == '' || $this->getUpdatedAt() == '00-00-0000 00:00:00' 
				|| $this->getUpdatedAt() == null)
			{
				$this->setUpdatedAt(date('Y-m-d H:i:s'));
			}
		}

		$reg = $this->getRegistry();

		$is_valid = false;
		$query = "SET IDENTITY_INSERT " . $this->getDBTable() . " ON insert into " . $this->getDBTable() . " (";
		$values = "values (";
		
		$i = 1;
		foreach($this->getColumns() as $col)
		{
			if(array_key_exists($col, $this->regDirty))
			{
				if(sizeof($this->regDirty) == 1)
				{
					$query .= $col . ") values (" . $this->db->quote($this->{$reg[$col]}) . ")";
					$values = "";
					$is_valid = true;
				}
				else if($i < sizeof($this->regDirty))
				{
					$query .= $col . ", ";
					$values .= $this->db->quote($this->{$reg[$col]}) . ", ";
					$is_valid = true;
				}
				else
				{
					$query .= $col . ") ";
					$values .= $this->db->quote($this->{$reg[$col]}) . ") ";
				}
				$i++;
			}
		}

		$query .= $values;
		$this->db->exec($query);

		$this->Id = $this->db->lastInsertId();
		$this->isNew = false;
		
		return $this->Id;
	}
}