<?php
/**
 * UptimeResponseLog
 *
 * *
 * @package    Dinkly
 * @subpackage ModelsCustomClasses
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class UptimeResponseLog extends BaseUptimeResponseLog
{
	public function getArray()
	{
		$output = array();
		$output['created_at'] = $this->getCreatedAt();
		$output['hash'] = $this->getHash();
		$output['source_ip_address'] = $this->getSourceIpAddress();
		$output['status'] = $this->getStatus();

		return $output;
	}
}

