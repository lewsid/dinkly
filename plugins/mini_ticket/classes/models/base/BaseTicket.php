<?php
/**
 * BaseTicket
 *
 * # This is an auto-generated file. Please do not alter this file. Instead, make changes to the model file that extends it.
 *
 * @package    Dinkly
 * @subpackage ModelsBaseClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class BaseTicket extends DinklyDataModel
{
	public $registry = array(
		'id' => 'Id',
		'created_at' => 'CreatedAt',
		'updated_at' => 'UpdatedAt',
		'associated_id' => 'AssociatedId',
		'user_id' => 'UserId',
		'first_name' => 'FirstName',
		'last_name' => 'LastName',
		'email' => 'Email',
		'subject' => 'Subject',
		'message' => 'Message',
		'status' => 'Status',
		'is_deleted' => 'IsDeleted',
		'session' => 'Session',
		'time_spent' => 'TimeSpent',
	);

	public $dbTable = 'ticket';
}

