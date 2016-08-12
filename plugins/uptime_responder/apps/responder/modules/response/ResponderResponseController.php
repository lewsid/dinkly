<?php
/**
 * ResponderResponseController
 *
 * @package    Dinkly
 * @subpackage AppsResponderResponseController
 * @author     Christopher Lewis <lewsid@lewsid.com>
 */
class ResponderResponseController extends ResponderController
{
	public function __construct()
	{
		parent::__construct();

		$this->db = DinklyDataConnector::fetchDB();
	}

	// http://stackoverflow.com/a/5965940/53079
	protected function arrayToXml($data, &$xml_data) 
	{
    	foreach($data as $key => $value) 
    	{
        	if(is_array($value))
        	{
            	if(is_numeric($key))
            	{
                	$key = 'item'.$key; //dealing with <0/>..<n/> issues
            	}
            	
            	$subnode = $xml_data->addChild($key);
            	array_to_xml($value, $subnode);
			}
	        else
	        {
	            $xml_data->addChild("$key",htmlspecialchars("$value"));
	        }
		}
	}

	/*
		Accepts GET variable 'format' with value 'string', 'json', or 'xml'

		Defaults to json

		/responder (outputs json)
		/responder/response/default/format/xml
		/responder/response/default/format/string
	*/
	public function loadDefault($parameters = array())
	{
		$log = new UptimeResponseLog($this->db);
		$log->setCreatedAt(date('Y-m-d G:i:s'));
		$log->setSourceIpAddress($_SERVER['REMOTE_ADDR']);
		$log->setStatus('OK');
		$log->setHash(md5(time()));
		$log->save();

		$this->output = null;

		if(isset($parameters['format']))
		{
			if($parameters['format'] == 'xml')
			{
				$xml = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
				$this->arrayToXml($log->getArray(), $xml);
				$this->output = $xml->asXML();
			}
			else if($parameters['format'] == 'string')
			{
				$this->output = $log->getStatus();
			}
			else if($parameters['format'] == 'json')
			{
				$this->output = json_encode($log->getArray());
			}
		}
		else { $this->output = json_encode($log->getArray()); }

		return true;
	}
}