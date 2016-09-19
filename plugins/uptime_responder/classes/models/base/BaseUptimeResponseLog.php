<?php
/**
 * BaseUptimeResponseLog
 *
 * # This is an auto-generated file. Please do not alter this file. Instead, make changes to the model file that extends it.
 *
 * @package    Dinkly
 * @subpackage ModelsBaseClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class BaseUptimeResponseLog extends DinklyDataModel
{
	public $registry = array(
		'id' => 'Id',
		'created_at' => 'CreatedAt',
		'source_ip_address' => 'SourceIpAddress',
		'status' => 'Status',
		'hash' => 'Hash',
	);

	public $dbTable = 'uptime_response_log';
}

