<?php

class DinklyFlashTest extends PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		date_default_timezone_set("Europe/Paris");
	}

	public function testExists()
	{
		DinklyFlash::set('exists_test_key_1','1');
		
		$this->assertTrue(DinklyFlash::exists('exists_test_key_1'));
		$this->assertFalse(DinklyFlash::exists('exists_test_key_hello'));

		DinklyFlash::clear();
	}

	public function testSet()
	{
		DinklyFlash::set('set_test_key_1','1');
		DinklyFlash::set('set_test_key_hello','world');
		
		$this->assertEquals(DinklyFlash::get('set_test_key_1'),'1');
		$this->assertEquals(DinklyFlash::get('set_test_key_hello'),'world');		

		DinklyFlash::clear();
	}

	public function testGet()
	{
		DinklyFlash::set('get_test_key_1','1');
		DinklyFlash::set('get_test_key_hello','world');
		
		$this->assertEquals(DinklyFlash::get('get_test_key_1'),'1');
		$this->assertEquals(DinklyFlash::get('get_test_key_hello'),'world');
		
		DinklyFlash::clear();
	}

	public function testGetAll()
	{	
		DinklyFlash::set('get_all_test_key_1','1');
		DinklyFlash::set('get_all_test_key_hello','world');
		
		$allDinklyFlashKeyValues = DinklyFlash::getAll($delete=false);
		$this->assertEquals($allDinklyFlashKeyValues['get_all_test_key_1'],'1');
		$this->assertEquals($allDinklyFlashKeyValues['get_all_test_key_hello'],'world');
		
		//calling DinklyFlash::getAll with no parameters will delete all key value pairs
		//in the session, and returns an array of the deleted key values pairs
		$allDinklyFlashKeyValuesDuplicated = DinklyFlash::getAll();
		$this->assertEquals($allDinklyFlashKeyValues, $allDinklyFlashKeyValuesDuplicated);
		
		//Dinkly Flash session values should now be empty
		$noMoreDinklyFlashKeyValues = DinklyFlash::getAll();
		$this->assertNotEquals($allDinklyFlashKeyValuesDuplicated,$noMoreDinklyFlashKeyValues);
		$this->assertEquals(DinklyFlash::getAll(),array());
		$this->assertEquals($_SESSION['dinkly']['flash'],array());		
	}

	public function testClear()
	{
		DinklyFlash::set('clear_test_key_1','1');
		DinklyFlash::set('clear_test_key_hello','world');
		
		$this->assertTrue(DinklyFlash::exists('clear_test_key_1'));
		$this->assertTrue(DinklyFlash::exists('clear_test_key_hello'));
		
		DinklyFlash::clear();
		
		$this->assertFalse(DinklyFlash::exists('clear_test_key_1'));
		$this->assertFalse(DinklyFlash::exists('clear_test_key_hello'));
	}
}